<?php

namespace Telegram;

class Event
{
    protected array $events = [];

    protected array $beforeRun = [];

    protected array $afterRun = [];

    public function __construct(protected Payload $payload)
    {
        //
    }

    public function on(string|array $event, callable|array $handler, int $order = 500): self
    {
        $this->events[$order][] = compact('event', 'handler');

        return $this;
    }

    public function onBeforeRun(callable|array $fn, array $args = [], int $order = 500)
    {
        $this->beforeRun[$order][] = compact('fn', 'args');
    }

    public function onAfterRun(callable|array $fn, array $args = [], int $order = 500)
    {
        $this->afterRun[$order][] = compact('fn', 'args');
    }

    public function run()
    {
        foreach ($this->arrayToFlattenByOrder($this->beforeRun) as $value) {
            call_user_func_array($value['fn'], $value['args']);
        }

        $this->handleEvents();

        foreach ($this->arrayToFlattenByOrder($this->afterRun) as $value) {
            call_user_func_array($value['fn'], $value['args']);
        }
    }

    protected function handleEvents()
    {
        foreach ($this->arrayToFlattenByOrder($this->events) as $item) {
            foreach ((array) $item['event'] as $key => $value) {
                // dump($key, $value, '---');

                /**
                 * [['key' => 'value'], ...]
                 */
                // if (is_array($value)) {
                //     $key = key($value);
                //     $value = $value[$key];
                // }

                if (is_numeric($key)) {
                    foreach ((array) $value as $variant) {
                        if ($this->payload->has($variant)) {
                            call_user_func_array($item['handler'], []);
                            break 2;
                        }
                    }
                }

                // dd($key, $value);

                $text = $this->payload->get($key);

                foreach ((array) $value as $variant) {
                    // tex* == text, texxx, etc...
                    if (mb_substr($variant, -1) == '*' && stripos($text, mb_substr($variant, 0, -1)) !== false) {
                        call_user_func_array($item['handler'], []);
                        break 2;
                    }

                    if ($text == $variant) {
                        call_user_func_array($item['handler'], []);
                        break 2;
                    }
                }

                foreach ((array) $value as $variant) {
                    $tmp = preg_replace('~.?{\?(.*?)}~m', '(?: ([\w\s]+))?', $variant);
                    $pattern = '~^' . preg_replace('~{(.*?)}~um', '([\w\s]+)', $tmp) . '$~um';

                    /**
                     * /ban {user} {?time}
                     */
                    if (@preg_match_all($pattern, $text, $matches)) {
                        call_user_func_array($item['handler'], $this->buildParamsFromMatches($matches));
                        break;
                    }

                    /**
                     * ['key' => '/regex/i]
                     */
                    if (@preg_match_all($variant, $text, $matches)) {
                        call_user_func_array($item['handler'], $this->buildParamsFromMatches($matches));
                        break;
                    }
                }


            }
        }
    }

    protected function arrayToFlattenByOrder(array $array): array
    {
        ksort($array);
        return call_user_func_array('array_merge', $array);
    }

    protected function buildParamsFromMatches(array $matches): array
    {
        return array_filter(array_map(function ($item) {
            return array_shift($item);
        }, array_slice($matches, 1)), 'strlen');
    }
}