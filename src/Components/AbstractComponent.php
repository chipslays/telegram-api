<?php

namespace Telegram\Components;

use Telegram\Bot;

abstract class AbstractComponent
{
    public function __construct(
        public Bot &$bot,
        public array $config,
        public string $path,
    ) {
        //
    }

    abstract public function __invoke(Bot $bot, array $config, string $path);
}