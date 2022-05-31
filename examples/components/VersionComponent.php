<?php

use Telegram\Bot;
use Telegram\Components\AbstractComponent;

class VersionComponent extends AbstractComponent
{
    public function __invoke(Bot $bot, array $config, string $path)
    {
        $bot->command('version', fn () => $bot->reply($config['version']));
    }
}

return VersionComponent::class;