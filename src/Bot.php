<?php

namespace Telegram;

use Telegram\BotApi\Client;
use Telegram\BotApi\Response;
use Telegram\BotApi\Traits\Methods as BotApiMethods;
use Telegram\BotApi\Traits\Aliases as BotApiMethodAliases;
use Telegram\BotApi\Traits\Replies as BotApiMethodReplies;
use Telegram\Plugins\Manager as PluginManager;
use Telegram\Traits\Eventable;
use Telegram\Support\Traits\Variable;
use Telegram\Exceptions\BotException;
use Illuminate\Support\Traits\Conditionable;
use Telegram\Traits\Componentable;
use Telegram\Traits\PluginMethods;

class Bot
{
    use Eventable;
    use Componentable;
    use BotApiMethods;
    use BotApiMethodAliases;
    use BotApiMethodReplies;
    use Conditionable;
    use Variable;
    use PluginMethods;

    public Payload $payload;

    protected Keyboard $keyboard;

    protected PluginManager $plugins;

    protected $components = [];

    public function __construct(
        public Client $api,
        public Config $config = new Config,
    )
    {
        $this->keyboard = new Keyboard($this);
        $this->plugins = new PluginManager($this);
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
     * @throws BotException
     */
    public function withWebhook(array $payload = null): self
    {
        if (is_array($payload)) {
            $this->payload = new Payload($payload, $this);
            return $this;
        }

        $payload = file_get_contents('php://input');

        if (!$payload) {
            throw new BotException('Payload from webhook is empty.', 1);
        }

        $this->payload = new Payload(json_decode($payload, true), $this);

        return $this;
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
            throw new BotException("Plugin '{$plugin}' not exists.");
        }

        return $instance;
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
}