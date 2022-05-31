<?php

use Telegram\Bot;
use Telegram\Components\AbstractComponent;

class Help extends AbstractComponent
{
    public function __invoke(Bot $bot, array $config, string $path)
    {
        $bot->command('help', fn () => $bot->reply('%help%'));
    }
}

return Help::class;