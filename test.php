<?php

use Telegram\Api;
use Telegram\Bot;
use Telegram\Database\Migrator;
use Telegram\Plugins\Database;
use Telegram\Plugins\Localization;
use Telegram\Plugins\Session;
use Telegram\Plugins\Storage;
use Telegram\Plugins\Telegraph;
use Telegram\Plugins\User;

require __DIR__ . '/vendor/autoload.php';

$api = new Api('AAAAAAAAA');

$bot = new Bot($api);

$bot->withWebhook([
    'from' => ['id' => '436432850', 'language_code' => 'ru'],
    'message' => [
        'text' => '.form',
    ],
]);

$bot->plugins([
    [
        'plugin' => Database::class,
        'config' => [
            'driver' => 'mysql',
            'drivers' => [
                'sqlite' => [
                    'prefix' => 'litegram_',
                    'database' => 'path/to/database.sqlite',
                ],
                'mysql' => [
                    'host' => 'localhost',
                    'prefix' => '',
                    'database' => 'litegram',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
                'pgsql' => [
                    'host' => 'localhost',
                    'prefix' => 'litegram_',
                    'database' => 'litegram',
                    'username' => 'litegram',
                    'password' => 'litegram',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
            ],
        ],
    ],
    [
        'plugin' => Localization::class,
        'config' => [
            'driver' => 'json',
            'fallback' => 'en',
            'path' => __DIR__ . '/examples/localization',
        ],
    ],
    [
        'plugin' => Storage::class,
        'config' => [
            'driver' => 'file',
            'file' => [
                'path' => __DIR__ . '/examples/storage',
            ],
        ],
    ],
    [
        'plugin' => Session::class,
    ],
    [
        'plugin' => User::class,
    ],
    [
        'plugin' => Telegraph::class,
    ],
]);

$bot->components([
    [
        'entrypoint' => __DIR__ . '/examples/components/version.php',
        'config' => ['foo' => 'bar'],
    ],
]);


// $migrator = new Migrator($bot);
// $migrator->up('users');


// $bot->storage(['test' => 'good']);
// dump($bot->storage('test'));

// $bot->session(['test' => 'good']);
// dump($bot->session('test'));

// $storage = $bot->plugin(Storage::class);
// $storage->set('foo', 'bar');
// dump($storage->get('foo'));

// $session = $bot->plugin(Session::class);
// $session->set('foo', 'bar1');
// dump($session->get('foo'));

// $localization = $bot->plugin(Localization::class);
// $localization->patch(__DIR__ . '/examples/localization/ru.1.json', 'ru', 'json');
// dump($localization->trans('HELLO', ['{name}' => 'Alex']));
// dump($bot->trans('HELLO', ['{name}' => 'Alex']));
// dump($localization->trans('BYE', ['{name}' => 'Alex']));

// $bot->on(['message.text' => 'Hello'], function ($bot) {
//     dump(123);
//     // $bot->sendMessage('436432850', 'v5.0');
// });

// $bot->command('start', function ($bot) {
//     dump('start!');
// });



// $bot->onBeforeRun(function () {
//     dump('before');
// });

// $bot->onAfterRun(function () {
//     dump('after');
// });

$bot->command('form', function ($bot) {
    // $bot->say('Отправь твое имя:');
    dump('Отправь твое имя:');
    $bot->conversation('form:name');
});

$bot->conversation('form:name', 'form:email', function ($bot) {
    // $bot->say('Отправь почту:');
    dump('Отправь почту:');
});

$bot->conversation('form:email', handler: function ($bot) {
    // $bot->say('Спасибо!');
    dump('Спасибо');
});

$bot->run();

// $bot->onFallback('message.text', function ($bot) {
//     $bot->say('Дефолтный ответ');
// });

// $bot->polling(function ($bot) {
//     print_r($bot->payload()->all());
// });
