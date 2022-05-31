<?php

use Telegram\Bot;
use Telegram\BotApi\Client;

require __DIR__ . '/../../vendor/autoload.php';

$api = new Client('BOT_TOKEN');

$bot = new Bot($api);

$bot->withWebhook([
    'message' => [
        'from' => ['id' => 436432850],
        'text' => '/start',
    ],
]);

$bot->plugins(require __DIR__ . '/plugins.config.php');

// handle events here

$bot->listen();