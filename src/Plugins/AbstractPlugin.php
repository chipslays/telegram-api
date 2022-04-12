<?php

namespace Telegram\Plugins;

use Telegram\Bot;

abstract class AbstractPlugin
{
    public function __construct(
        protected Bot &$bot,
        public array $config = []
    ) {
        $this->bot = $bot;
    }

    public function boot(): void
    {
    }

    public function onBeforeRun(): void
    {
    }

    public function onAfterRun(): void
    {
    }
}
