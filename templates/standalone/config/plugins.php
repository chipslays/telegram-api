<?php

use Telegram\Plugins\Database;
use Telegram\Plugins\Localization;
use Telegram\Plugins\Logger;
use Telegram\Plugins\Session;
use Telegram\Plugins\Storage;
use Telegram\Plugins\Telegraph;
use Telegram\Plugins\User;

return [
    [
        'plugin' => Database::class,
        'config' => [
            'driver' => 'mysql',
            'drivers' => [
                'sqlite' => [
                    'prefix' => '',
                    'database' => 'path/to/database.sqlite',
                ],
                'mysql' => [
                    'host' => 'localhost',
                    'prefix' => '',
                    'database' => 'telegram',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
                'pgsql' => [
                    'host' => 'localhost',
                    'prefix' => '',
                    'database' => 'telegram',
                    'username' => 'root',
                    'password' => '',
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
            'path' => __DIR__ . '/../bot/Localization',
        ],
    ],
    [
        'plugin' => Logger::class,
        'config' => [
            'path' => __DIR__ . '/../storage/logs',
            'payload_log' => true,
        ],
    ],
    [
        'plugin' => Storage::class,
        'config' => [
            'driver' => 'file',
            'file' => [
                'path' => __DIR__ . '/../storage/store',
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
];