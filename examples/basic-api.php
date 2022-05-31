<?php

use Telegram\BotApi\Client;
use Telegram\Support\InputFile;

require __DIR__ . '/../vendor/autoload.php';

/**
 * @see https://core.telegram.org/bots/api#available-methods List of all methods.
 */

$api = new Client('BOT_TOKEN');

$api->sendMessage(436432850, 'Hello World!');

$api->sendPhoto(436432850, new InputFile('/photos/nudes.jpg'), 'Do you like it?');


