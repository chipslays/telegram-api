<?php

use Telegram\Bot;
use Telegram\BotApi\Client;

require __DIR__ . '/../../vendor/autoload.php';

$api = new Client('1436866061:AAGpbiuM0gsYc1uH-DdI5uYsCStl7e9NFZY');

$bot = new Bot($api);

$bot->withWebhook();

$bot->hear('hello', function (Bot $bot) {
    $bot->reply('Hello World!');
});

/** Commands */
$bot->command('/say {text}', function (Bot $bot, $text) {
    $bot->reply('<b>' . $text . '</b>');
});

/** Callback action */
$bot->command('/action', function (Bot $bot, $text) {
    $bot->reply('Callback action:', [
        [
            ['text' => 'Show alert', 'callback_data' => 'test'],
        ],
    ]);
});

$bot->action('test', function (Bot $bot) {
    $bot->alert('You press button.');
});

/** Inline query */
$bot->inline('deadpool 2016', function (Bot $bot) {
    /** show films as inline answer */
});

$bot->listen();