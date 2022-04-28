<?php

namespace Telegram;

use Telegram\Plugins\Manager as PluginManager;
use Telegram\Plugins\Database;
use Telegram\Plugins\Session;
use Telegram\Plugins\Storage;
use Telegram\Plugins\Localization;
use Telegram\Traits\BotApiMethods;
use Illuminate\Database\Connection;
use Illuminate\Support\Traits\Conditionable;
use Telegram\Plugins\Telegraph;
use Telegram\Support\Traits\Variable;
use Telegram\Traits\Replies;
use Telegram\Traits\MethodsAliases;
use Telegram\Plugins\Logger;
use Telegram\Plugins\User;
use Exception;

class Bot
{
    use BotApiMethods;
    use MethodsAliases;
    use Replies;
    use Variable;
    use Conditionable;

    protected $events = [];

    protected $middlewares = [];

    protected $components = [];

    protected $beforeRun = [];

    protected $afterRun = [];

    protected $fallbackRun = [];

    protected bool $eventsIsSkipped = false;

    protected Keyboard $keyboard;

    protected PluginManager $plugins;

    /**
     * Constructor.
     *
     * @param Api $api
     * @param Config $config
     */
    public function __construct(
        protected Api $api,
        protected Config $config = new Config
    ) {
        $this->keyboard = new Keyboard($this);
        $this->plugins = new PluginManager($this);
    }

    /**
     * Load plugin or get plugin manager.
     *
     * @param array|null $plugins
     * @return Manager|void
     */
    public function plugins(array $plugins = null)
    {
        return $plugins ? $this->plugins->load($plugins) : $this->plugins;
    }

    /**
     * Get a plugin instance.
     *
     * E.g. $bot->plugin(ExamplePlugin::class)
     *
     * @param string $plugin
     * @return void
     */
    public function plugin(string $plugin)
    {
        $instance = $this->plugins->get($plugin);

        if (!$instance) {
            throw new Exception("Plugin '{$plugin}' not exists.");
        }

        return $instance;
    }

    /**
     * Raw call Telegram Bot API method.
     *
     * @param string $method
     * @param array $parameters
     * @return Response
     */
    public function method(string $method, array $parameters = []): Response
    {
        return $this->api->method($method, $parameters);
    }

    /**
     * Get incoming payload from Telegram webhook.
     *
     * @param array|null $payload
     * @return self
     *
     * @throws Exception
     */
    public function withWebhook(array $payload = null): self
    {
        if (is_array($payload)) {
            $this->payload = new Payload($payload, $this);
            $this->boot();

            return $this;
        }

        $payload = file_get_contents('php://input');

        if (!$payload) {
            throw new Exception('No webhook updates.');
        }

        $this->payload = new Payload(json_decode($payload, true), $this);
        $this->boot();

        return $this;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function boot()
    {
        if ($this->payload()->isCallbackQuery()) {
            $this->userId = $this->payload('*.message.chat.id');
            $this->languageId = $this->payload('*.message.chat.language_code', $this->payload('*.message.reply_to_message.from.language_code'));
        } else {
            $this->userId = $this->payload('*.from.id', $this->payload('*.user.id', $this->payload('*.chat.id')));
            $this->languageId = $this->payload('*.from.language_code', $this->payload('*.user.language_code'));
        }

        if (!$this->userId) {
            throw new Exception('User ID can\'t be a NULL.');
        }
    }
    
    public function getChatId()
    {
        return $this->userId;
    }

    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * Get config value or `Config` instance.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed|Config
     */
    public function config(string $key = null, $default = null)
    {
        return $key ? $this->config->get($key, $default) : $this->config;
    }

    /**
     * Get payload value or `Payload` instance.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed|Payload
     */
    public function payload(string $key = null, $default = null)
    {
        return $key ? $this->payload->get($key, $default) : $this->payload;
    }

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
            $fired = $this->processEvents();

            if (!$fired) {
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
        $fired = false;

        ksort($this->events);
        $events = call_user_func_array('array_merge', $this->events);

        foreach ($events as $event) {
            foreach ((array) $event['pattern'] as $key => $value) {
                if ($value === true) {
                    $fired = true;
                    if ($this->fireEvent($event) === false) {
                        return $fired;
                    }
                    break;
                }

                // 'message.text'
                // ['message', 'callback_query]
                if (is_numeric($key) && $this->payload()->has($value)) {
                    $fired = true;
                    if ($this->fireEvent($event) === false) {
                        return $fired;
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
                            $fired = true;
                            if ($this->fireEvent($event) === false) {
                                return $fired;
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $fired;
    }

    public function onBeforeRun(callable $fn, int $sort = 500)
    {
        $this->beforeRun[$sort][] = $fn;
    }

    public function onAfterRun(callable $fn, int $sort = 500)
    {
        $this->afterRun[$sort][] = $fn;
    }

    public function onFallback(string|array $pattern, callable $fn)
    {
        $this->fallbackRun[] = compact('pattern', 'fn');
    }

    protected function runComponents()
    {
        foreach ($this->components as $component) {
            if (file_exists($component['entrypoint'] ?? null)) {
                $fn = function ($bot, $config) use ($component) {
                    require $component['entrypoint'];
                };
                call_user_func_array($fn, [$this, $component['config'] ?? []]);
            }
        }
    }

    public function fireEvent(array $event)
    {
        if (count($event['middlewares']) > 0) {
            $middleware = array_shift($event['middlewares']);

            if (is_callable($middleware)) {
                return call_user_func_array($middleware, [$this, $event, [$this, 'fireEvent']]);
            }

            $middleware = $this->middlewares[$middleware];
            return call_user_func_array($middleware, [$this, $event, [$this, 'fireEvent']]);
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
     * Before call a massive operations, use this method for non-block bot answers.
     *
     * @param array $response Response to incoming webhook update.
     * @param int $timeLimit Execution script time limit in seconds.
     * @return void
     */
    public function closeRequest(array $response = ['ok'], int $timelimit = 3600): void
    {
        set_time_limit($timelimit);
        ignore_user_abort(true);

        $response = json_encode($response);

        if (function_exists('fastcgi_finish_request')) {
            echo $response;
            fastcgi_finish_request();
        } else {
            ob_start();
            header('Connection: close');
            header('Content-Type: application/json; charset=utf-8');
            echo $response;

            $length = ob_get_length();
            header('Content-Length: ' . $length);
            ob_end_flush();
            flush();
        }
    }

    public function listen(): void
    {
        $this->closeRequest();
        $this->run();
    }

    /**
     * Polling Telegram updates (long-polling).
     *
     * @param callable $callback{$bot}
     * @param array $extra
     * @return void
     */
    public function polling(callable $callback = null, array $getUpdatesExtra = [])
    {
        Terminal::print('{text:darkGreen}Polling started...');

        $offset = 0;

        while (true) {
            $updates = $this->getUpdates($offset, 100, $getUpdatesExtra);
            foreach ($updates as $payload) {
                $offset = $payload['update_id'] + 1;
                $this->payload = new Payload($payload, $this);
                $this->boot();

                if ($callback) {
                    call_user_func_array($callback, [$this]);
                }

                $this->run();

                // reload plugins
                // $this->plugins->reload();
            }
        }
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

    /**
     * Set components.
     *
     * @param array $components
     * @return void
     */
    public function components(array $components): void
    {
        $this->components = array_merge($this->components, $components);
    }

    /**
     * @param string|integer|array|null $key
     * @param mixed $default
     * @return mixed|Storage
     */
    public function storage(string|int|array $key = null, mixed $default = null)
    {
        $storage = $this->plugin(Storage::class);

        if ($key === null) {
            return $storage;
        }

        if (is_array($key)) {
            foreach ($key as $index => $value) {
                $storage->set($index, $value);
            }
            return;
        }

        return $storage->get($key, $default);
    }

    /**
     * @param string|integer|array|null $key
     * @param mixed $default
     * @return mixed|Session
     */
    public function session(string|int|array $key = null, mixed $default = null)
    {
        $session = $this->plugin(Session::class);

        if ($key === null) {
            return $session;
        }

        if (is_array($key)) {
            foreach ($key as $index => $value) {
                $session->set($index, $value);
            }
            return;
        }

        return $session->get($key, $default);
    }

    /**
     * @return Localization
     */
    public function localization(): Localization
    {
        return $this->plugin(Localization::class);
    }

    /**
     * @param string $key
     * @param array|null $replacements
     * @param string|null $locale
     * @return string
     */
    public function trans(string $key, ?array $replacements = null, string $locale = null): string
    {
        return $this->localization()->trans($key, $replacements, $locale);
    }

    /**
     * @return Telegraph
     */
    public function telegraph(): Telegraph
    {
        return $this->plugin(Telegraph::class);
    }

    /**
     * @param string|array $key
     * @param mixed $default
     * @return mixed|User
     */
    public function user(string|array $key = null, mixed $default = null): mixed
    {
        /** @var User */
        $user = $this->plugin(User::class);

        if ($key === null) {
            return $user;
        }

        if (is_array($key)) {
            return $user->model()->update($key);
        }

        return $user->get((string) $key, $default);
    }

    /**
     * @return Logger
     */
    public function log(mixed $data, string $title = 'default', string $filePostfix = 'bot'): void
    {
        /** @var Logger */
        $logger = $this->plugin(Logger::class);
        $logger->put($data, $title, $filePostfix);
    }

    /**
     * Get database connection.
     *
     * @return Connection
     */
    public function db(string $connetion = 'default'): Connection
    {
        $database = $this->plugin(Database::class);

        return $database->connection($connetion);
    }

    /**
     * Get `Keyboard` instance or make keyboard (by method `Keyboard::show(...)`) if arguments was passed.
     *
     * @param array|string $keyboard
     * @param string|null $placeholder
     * @param boolean $oneTime
     * @param boolean $resize
     * @param boolean $selective
     * @return string|Keyboard
     */
    public function keyboard(
        array|string $keyboard = null,
        ?string $placeholder = null,
        bool $oneTime = false,
        bool $resize = true,
        bool $selective = false
    ): string|Keyboard
    {
        if (func_num_args() === 0 || $keyboard === null) {
            return $this->keyboard;
        }

        return $this->keyboard->show($keyboard, $placeholder, $oneTime, $resize, $selective);
    }

    /**
     * @param boolean|string $needle
     * @param string|null|null $next
     * @param callable|null $handler
     * @param array $excepts
     * @return self
     */
    public function conversation(bool|string $needle, string|null $next = null, callable $handler = null, array $excepts = []): self
    {
        if ($needle === false) {
            $this->session()->delete('telegram:conversation');
            return $this;
        }

        if ($this->hasConversationMatchExcepts($excepts)) {
            return $this;
        }

        if (func_num_args() == 1) {
            $this->session(['telegram:conversation' => $needle]);
            $this->var(['telegram:conversation_skip' => true]);
            return $this;
        }

        if ($this->var('telegram:conversation_skip')) {
            return $this;
        }

        if ($this->session('telegram:conversation') !== $needle) {
            return $this;
        }

        $this->var(['telegram:conversation_skip' => true]);

        $result = call_user_func_array($handler, [$this]);

        if ($result !== false) {
            if ($next === null) {
                $this->session()->delete('telegram:conversation');
            } else {
                $this->session(['telegram:conversation' => $next]);
            }
        }

        $this->skipEvents();

        return $this;
    }

    /**
     * @param array $excepts
     * @return boolean
     */
    protected function hasConversationMatchExcepts(array $excepts): bool
    {
        foreach ($excepts as $event) {
            foreach ((array) $event as $key => $value) {
                if ($value === true) {
                    return true;
                }

                // 'message.text'
                // ['message', 'callback_query]
                if (is_numeric($key) && $this->payload()->has($value)) {
                    return true;
                }

                // ['message.text' => 'text']
                // ['message.text' => ['text1', 'text2]]
                if (!is_numeric($key)) {
                    foreach ((array) $value as $needle) {
                        $result = $this->match($needle, $this->payload($key, ''));
                        if ($result !== false) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function skipEvents()
    {
        $this->eventsIsSkipped = true;
    }

    /**
     * @return void
     */
    public function unskipEvents()
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
}
