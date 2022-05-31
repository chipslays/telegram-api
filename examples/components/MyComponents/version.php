<?php

use Telegram\Bot;

/** @var Bot $bot */
/** @var array $config */

$bot->command('version', function (Bot $bot) {
    $bot->reply('Version: 1.0.0');
});