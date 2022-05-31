<?php

use Telegram\Bot;
use Telegram\Components\AbstractComponent;

class Version extends AbstractComponent
{
    public function __invoke(Bot $bot, array $config, string $path)
    {
        $bot->command(['version', 'v'], fn () => $bot->reply('%version%'));
    }
}

return Version::class;