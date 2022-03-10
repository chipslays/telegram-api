<?php

namespace Telegram;

use Telegram\Support\Collection;

class Config extends Collection
{
    protected array $default = [
        'bot' => [
            'handler' => 'https://bot.example.com/handler.php',
            'prefix' => ['/', '!', '.'],
        ],
    ];

    public function __construct(array $config = [])
    {
        $this->items = array_replace_recursive($this->default, $config);
    }
}