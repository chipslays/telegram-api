<?php

namespace Telegram\Plugins;

use Telegram\Bot;

class Manager
{
    protected array $plugins = [];

    protected array $config = [];

    public function __construct(protected Bot $bot)
    {

    }

    public function load(array $plugins)
    {
        foreach ($plugins as $data) {
            $instance = new $data['plugin']($this->bot, $data['config'] ?? []);
            call_user_func([$instance, 'boot']);
            $this->plugins[$data['plugin']] = $instance;
            $this->config[$data['plugin']] = $data['config'] ?? [];
        }
    }

    public function config(string $plugin)
    {
        return $this->config[$plugin];
    }

    public function get(string $plugin)
    {
        return $this->plugins[$plugin] ?? null;
    }

    public function has(string $plugin)
    {
        return isset($this->plugins[$plugin]);
    }

    public function all()
    {
        return $this->plugins;
    }
}
