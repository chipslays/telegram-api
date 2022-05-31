<?php

namespace Telegram\Traits;

trait Eventable
{
    protected array $events = [];

    protected $middlewares = [];

    protected $beforeRun = [];

    protected $afterRun = [];

    protected $fallbackRun = [];

    protected bool $eventsIsSkipped = false;

    /**
     * @param string|array $pattern
     * @param callable $handler
     * @param array $middlewares
     * @param integer $sort
     * @return self
     */
    public function on(string|array $pattern, callable $handler, array $middlewares = [], int $sort = 500): self
    {
        $this->events[$sort][] = compact('pattern', 'handler', 'middlewares');

        return $this;
    }

    public function run()
    {
        $this->runComponents();

        $beforeRun = $this->beforeRun;
        ksort($beforeRun);
        $beforeRun = call_user_func_array('array_merge', $beforeRun);
        foreach ($beforeRun as $fn) {
            call_user_func_array($fn, [$this]);
        }

        foreach ($this->plugins->all() as $plugin) {
            call_user_func([$plugin, 'onBeforeRun']);
        }

        if (!$this->eventsIsSkipped()) {
            $processed = $this->processEvents();

            if (!$processed) {
                foreach ($this->fallbackRun as $fallback) {
                    foreach ((array) $fallback['pattern'] as $pattern) {
                        if ($this->payload()->has($pattern)) {
                            call_user_func_array($fallback['fn'], [$this]);
                            break;
                        }
                    }
                }
            }
        }

        // reset for polling
        $this->unskipEvents();

        $afterRun = $this->afterRun;
        ksort($afterRun);
        $afterRun = call_user_func_array('array_merge', $afterRun);
        foreach ($afterRun as $fn) {
            call_user_func_array($fn, [$this]);
        }

        foreach ($this->plugins->all() as $plugin) {
            call_user_func([$plugin, 'onAfterRun']);
        }

        // reset for polling
        $this->events = [];
        $this->beforeRun = [];
        $this->afterRun = [];
    }

    protected function processEvents()
    {
        $processed = false;

        ksort($this->events);
        $events = call_user_func_array('array_merge', $this->events);

        foreach ($events as $event) {
            foreach ((array) $event['pattern'] as $key => $value) {
                if ($value === true) {
                    $processed = true;
                    if ($this->executeEvent($event) === false) {
                        return $processed;
                    }
                    break;
                }

                // 'message.text'
                // ['message', 'callback_query]
                if (is_numeric($key) && $this->payload()->has($value)) {
                    $processed = true;
                    if ($this->executeEvent($event) === false) {
                        return $processed;
                    }
                    break;
                }

                // ['message.text' => 'text']
                // ['message.text' => ['text1', 'text2]]
                if (!is_numeric($key)) {
                    foreach ((array) $value as $needle) {
                        $result = $this->match($needle, $this->payload($key, ''));
                        if ($result !== false) {
                            $event['args'] = $result;
                            $processed = true;
                            if ($this->executeEvent($event) === false) {
                                return $processed;
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $processed;
    }

    public function executeEvent(array $event)
    {
        if (count($event['middlewares']) > 0) {
            $middleware = array_shift($event['middlewares']);

            if (is_callable($middleware)) {
                return call_user_func_array($middleware, [$this, $event, [$this, 'executeEvent']]);
            }

            $middleware = $this->middlewares[$middleware];
            return call_user_func_array($middleware, [$this, $event, [$this, 'executeEvent']]);
        }

        if (!isset($event['args']) || is_bool($event['args'])) {
            $event['args'] = [];
        }

        $args = array_merge([$this], $event['args']);
        return call_user_func_array($event['handler'], $args);
    }

    /**
     * Match needle value in haystack.
     *
     * @param string $needle
     * @param string $haystack
     * @return boolean|array
     */
    public function match(string $needle, string $haystack): bool|array
    {
        // text == text
        if ($needle == $haystack) {
            return true;
        }

        // tex* == text, texxx, etc...
        if (mb_substr($needle, -1) == '*') {
            return stripos($haystack, mb_substr($needle, 0, -1)) !== false;
        }

        // /ban {user} {?time}
        // $tmp = preg_replace('~.?{\?(.*?)}~m', '(?:([\w\s]+))?', $needle);
        $tmp = preg_replace('~.?{\?(.*?)}~m', '(?: ([\w\s]+))?', $needle);
        $pattern = '~^' . preg_replace('~{(.*?)}~um', '([\w\s]+)', $tmp) . '$~um';

        if (@preg_match_all($pattern, $haystack, $matches)) {
            return array_filter(array_map(function ($item) {
                return array_shift($item);
            }, array_slice($matches, 1)), 'strlen');
        }

        // regex pattern
        if (@preg_match_all($needle, $haystack, $matches)) {
            return array_filter(array_map(function ($item) {
                return array_shift($item);
            }, array_slice($matches, 1)), 'strlen');
        }

        return false;
    }

    /**
     * @param callable $fn
     * @param integer $sort
     * @return void
     */
    public function onBeforeRun(callable $fn, int $sort = 500): void
    {
        $this->beforeRun[$sort][] = $fn;
    }

    /**
     * @param callable $fn
     * @param integer $sort
     * @return void
     */
    public function onAfterRun(callable $fn, int $sort = 500): void
    {
        $this->afterRun[$sort][] = $fn;
    }

    /**
     * Default answer if event not found.
     *
     * @param string|array $pattern
     * @param callable $fn
     * @return void
     */
    public function fallback(string|array $pattern, callable $fn): void
    {
        $this->fallbackRun[] = compact('pattern', 'fn');
    }

    /**
     * @return void
     */
    public function skipEvents(): void
    {
        $this->eventsIsSkipped = true;
    }

    /**
     * @return void
     */
    public function unskipEvents(): void
    {
        $this->eventsIsSkipped = false;
    }

    /**
     * @return boolean
     */
    public function eventsIsSkipped(): bool
    {
        return $this->eventsIsSkipped;
    }

    /**
     * Hear simple text messages.
     *
     * @param string|array $pattern
     * @param callable $handler
     * @param array $middlewares
     * @param integer $sort
     * @return self
     */
    public function hear(string|array $pattern, callable $handler, array $middlewares = [], int $sort = 500): self
    {
        return $this->when(
            $this->payload()->isMessage() && $this->payload()->isCommand() === false,
            fn () => $this->on(['message.text' => $pattern], $handler, $middlewares, $sort),
        );
    }

    /**
     * Hear only command messages.
     *
     * Define prefixes in config `bot.prefix` section.
     *
     * Default `bot.prefix = ['/', '!', '.']`.
     *
     * A command is a message starting with one of these prefixes.
     *
     * @param string|array $pattern
     * @param callable $handler
     * @param array $middlewares
     * @param integer $sort
     * @return self
     */
    public function command(string|array $pattern, callable $handler, array $middlewares = [], int $sort = 500): self
    {
        if (!$this->payload()->isMessage()) {
            return $this;
        }

        $prefixes = $this->config('bot.prefix');

        $commands = ['message.text' => []];

        $firstChar = substr($this->payload('message.text'), 0, 1);

        if (!in_array($firstChar, $prefixes)) {
            return $this;
        }

        foreach ((array) $pattern as $command) {
            $commands['message.text'] = array_merge(
                $commands['message.text'],
                array_map(fn ($prefix) => $prefix . $command, $prefixes)
            );
        }

        return $this->on($commands, $handler, $middlewares, $sort);
    }

    /**
     * Hear only callback actions (click on inline button and etc...).
     *
     * @param string|array $pattern
     * @param callable $handler
     * @param array $middlewares
     * @param integer $sort
     * @return self
     */
    public function action(string|array $pattern, callable $handler, array $middlewares = [], int $sort = 500): self
    {
        return $this->when(
            $this->payload()->isCallbackQuery(),
            fn () => $this->on(['callback_query.data' => $pattern], $handler, $middlewares, $sort),
        );
    }

    /**
     * Hear only inline queries.
     *
     * @param string|array $pattern
     * @param callable $handler
     * @param array $middlewares
     * @param integer $sort
     * @return self
     */
    public function inline(string|array $pattern, callable $handler, array $middlewares = [], int $sort = 500): self
    {
        return $this->when(
            $this->payload()->isInlineQuery(),
            fn () => $this->on(['inline_query.query' => $pattern], $handler, $middlewares, $sort),
        );
    }
}