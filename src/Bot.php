<?php

namespace Telegram;


class Bot
{
    public readonly BotApi|string $api;

    public readonly Config $config;

    public readonly Event $event;

    /**
     * Constructor.
     *
     * @param BotApi|string $api BotApi instance or bot token.
     * @param Config|null $config
     */
    public function __construct(BotApi|string $api, Config $config = null)
    {
        $this->api = is_string($api) ? new BotApi($api) : $api;

        $this->config = $config ?? new Config;

        $this->event = new Event;
    }
}