<?php

/** @var Telegram\Bot $bot  */

$bot->command('version', function () {
    dump('v1.3.3');
});