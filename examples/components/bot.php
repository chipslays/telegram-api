<?php

use Telegram\Bot;
use Telegram\BotApi\Client;

require __DIR__ . '/../../vendor/autoload.php';

$api = new Client('BOT_TOKEN');

$bot = new Bot($api);

$bot->withWebhook([
    'message' => [
        'from' => ['id' => 436432850],
        'text' => '/version',
    ],
]);

$bot->components([
    [
        'entrypoint' => __DIR__ . '/VersionComponent.php',
        'config' => [
            'version' => '1.0.0',
        ],
    ] ,
]);

$bot->listen();