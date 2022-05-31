<?php

use Telegram\Bot;
use Telegram\BotApi\Client;
use Telegram\Config;
use Telegram\Manager;

require_once __DIR__ . '/../vendor/autoload.php';

$config = new Config(require_once __DIR__ . '/../config/bot.php');

$api = new Client($config->bot['token']);
$bot = new Bot($api, $config);

Manager::add($bot);

$bot->withWebhook();

$bot->keyboard()->add(require_once __DIR__ . '/Keyboards/ReplyKeyboards.php');
$bot->keyboard()->add(require_once __DIR__ . '/Keyboards/InlineKeyboards.php');

$bot->plugins(require_once __DIR__ . '/../config/plugins.php');

$bot->components([
    ['entrypoint' => __DIR__ . '/Components/Start.php'],
    ['entrypoint' => __DIR__ . '/Components/Help.php'],
    ['entrypoint' => __DIR__ . '/Components/Version.php'],
]);

$bot->listen();
