<?php

return [
    'id' => 'app',
    'basePath' => dirname(__DIR__, 2),
    'timeZone' => 'Asia/Jakarta',
    'language' => 'id-ID',
    'aliases' => [
        '@storage' => dirname(__DIR__, 2) .'/storage',
    ],
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'cache' => 'yii\caching\FileCache',
        'db' => [
            'charset' => 'utf8',
            'class' => 'yii\db\Connection',
            'dsn' => $_ENV['DB_DSN'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'tablePrefix' => $_ENV['DB_TABEL_PREFIX'] ?? '',
        ],
    ],
    'params' => [
        'encrypt_key' => $_ENV['ENCRYPT_KEY'],
    ],
];