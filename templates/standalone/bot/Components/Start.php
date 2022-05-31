<?php

use Telegram\Bot;
use Telegram\Components\AbstractComponent;

class Start extends AbstractComponent
{
    public function __invoke(Bot $bot, array $config, string $path)
    {
        $bot->command('start', fn () => $bot->reply('%start%'));
    }
}

return Start::class;