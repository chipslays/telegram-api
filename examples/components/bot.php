<?php

use Telegram\Bot;
use Telegram\BotApi\Client;

require __DIR__ . '/../../vendor/autoload.php';

$api = new Client('1436866061:AAGpbiuM0gsYc1uH-DdI5uYsCStl7e9NFZY');

$bot = new Bot($api);

$bot->withWebhook([
    'message' => [
        'from' => ['id' => 436432850],
        'text' => '/version',
    ],
]);

$bot->components([
    [
        'entrypoint' => __DIR__ . '/MyComponents/version.php',
        'config' => [],
    ] ,
]);

$bot->listen();