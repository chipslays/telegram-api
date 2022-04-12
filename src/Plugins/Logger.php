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
        if ($this->config['payload_log'] ?? false) {
            $this->put($this->bot->payload()->toArray(), 'auto', 'payload');
        };
    }

    /**
     * Put data to log file.
     *
     * @param mixed $data
     * @param string $title
     * @param string $filePostfix
     * @return void
     */
    public function put(mixed $data, string $title = 'default', string $filePostfix = 'bot'): void
    {
        $currentYear = date('Y');
        $currentMonth = date('F');

        $path = rtrim($this->config['path'], '\\/');
        $path = "{$path}/{$currentYear}/{$currentMonth}";

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $data = is_array($data) ? "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ' ' . trim($data);
        $date = date("d.m.Y, H:i:s");
        $log = "[{$date}] [{$title}]{$data}";

        $filename = date("Y-m-d") . "_{$filePostfix}.log";

        file_put_contents($path . "/{$filename}", $log . PHP_EOL, FILE_APPEND);
    }
}