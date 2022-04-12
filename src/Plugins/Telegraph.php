<?php

namespace Telegram\Plugins;

use Telegram\Bot;
use Chipslays\Telegraph\Client;

class Telegraph extends Client
{
    public function __construct(
        protected Bot &$bot,
        public array $config = []
    ) {
        parent::__construct();
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