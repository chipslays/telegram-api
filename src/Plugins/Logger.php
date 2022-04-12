<?php

namespace Telegram\Plugins;

class Logger extends AbstractPlugin
{
    public function boot(): void
    {
        //
    }

    public function onBeforeRun(): void
    {
        //
    }

    public function onAfterRun(): void
    {
        $path = $this->config['path'] ?? 'none';
        dump($path);
    }
}