<?php

namespace Telegram;

use Exception;

class Manager
{
/**
     * Array with stored bots.
     *
     * @var array
     */
    protected static array $bots = [];

    /**
     * Add bot instance to manager.
     *
     * @param Bot $bot
     * @param string $name
     * @return void
     */
    public static function add(Bot $bot, string $name = 'default'): void
    {
        self::$bots[$name] = $bot;
    }

    /**
     * Getting saved bot instance from manager for interact.
     *
     * @param string $name
     * @return Bot
     *
     * @throws Exception
     */
    public static function get(string $name = 'default'): Bot
    {
        if (!array_key_exists($name, self::$bots)) {
            throw new Exception("Bot with name '{$name}' not exists in Manager.", 1);
        }

        return self::$bots[$name];
    }

    /**
     * Remove saved bot instance from manager.
     *
     * @param string $name
     * @return void
     */
    public static function remove(string $name): void
    {
        unset(self::$bots[$name]);
    }

    /**
     * Get all bots.
     *
     * @return Bot[]
     */
    public static function all(): array
    {
        return self::$bots;
    }

    /**
     * Getting saved bot instance from manager for interact.
     *
     * @param string $name
     * @return Bot
     *
     * @throws Exception
     */
    public function __invoke(string $name = 'default')
    {
        return self::get($name);
    }
}