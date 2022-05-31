<?php

namespace Telegram;

use Telegram\BotApi\Client;
use Telegram\BotApi\Response;
use Telegram\BotApi\Traits\Methods as BotApiMethods;
use Telegram\BotApi\Traits\Aliases as BotApiMethodAliases;
use Telegram\BotApi\Traits\Replies as BotApiMethodReplies;
use Telegram\Traits\Eventable;
use Telegram\Support\Traits\Variable;
use Telegram\Exceptions\BotException;
use Illuminate\Support\Traits\Conditionable;

class Bot
{
    use Eventable;
    use BotApiMethods;
    use BotApiMethodAliases;
    use BotApiMethodReplies;
    use Conditionable;
    use Variable;

    public Payload $payload;

    protected $components = [];

    public function __construct(
        public Client $api,
        public Config $config = new Config,
    )
    {
        //
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
}