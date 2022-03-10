<?php

use Telegram\Api;
use Telegram\Bot;
use Telegram\Plugins\Localization;
use Telegram\Plugins\Session;
use Telegram\Plugins\Storage;

require __DIR__ . '/vendor/autoload.php';

$api = new Api('1436866061:AAEaJrIqLkfzqA2E0vtdj2i4UkdvJAY7p68');

$bot = new Bot($api);

$bot->withWebhook([
    'from' => ['id' => '436432850', 'language_code' => 'ru'],
    'message' => [
        'text' => '.version',
    ],
]);

$bot->plugins([
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
]);

$bot->components([
    [
        'entrypoint' => __DIR__ . '/examples/components/version.php',
        'config' => ['foo' => 'bar'],
    ],
]);

$bot->storage(['test' => 'good']);
dump($bot->storage('test'));

$bot->session(['test' => 'good']);
dump($bot->session('test'));

$storage = $bot->plugin(Storage::class);
$storage->set('foo', 'bar');
dump($storage->get('foo'));

$session = $bot->plugin(Session::class);
$session->set('foo', 'bar1');
dump($session->get('foo'));

$localization = $bot->plugin(Localization::class);
$localization->patch(__DIR__ . '/examples/localization/ru.1.json', 'ru', 'json');
dump($localization->trans('HELLO', ['{name}' => 'Alex']));
dump($bot->trans('HELLO', ['{name}' => 'Alex']));
dump($localization->trans('BYE', ['{name}' => 'Alex']));

$bot->on(['message.text' => 'Hello'], function ($bot) {
    dump(123);
    // $bot->sendMessage('436432850', 'v5.0');
});

$bot->command('start', function ($bot) {
    dump('start!');
});

$bot->onBeforeRun(function () {
    dump('before');
});

$bot->onAfterRun(function () {
    dump('after');
});

$bot->onFallback('message.text', function () {
    dump('default');
});

$bot->run();

// $bot->polling(function ($bot) {
//     print_r($bot->payload()->all());

//     $bot->hear('hello', function ($bot) {
//         dump(123);
//         $bot->sendMessage('436432850', 'v5.0');
//     });
// });