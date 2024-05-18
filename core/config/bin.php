<?php

use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

$_ENV = $_SERVER;

require __DIR__ .'/bootstrap.php';

$params = require __DIR__ .'/params.php';

$config = [
    'id' => 'basic-console',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'controllerMap' => require __DIR__.'/commands.php',
    'components' => [
        'setting' => 'app\domains\setting\Setting',
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    // $config['bootstrap'][] = 'gii';
    // $config['modules']['gii'] = [
    //     'class' => 'yii\gii\Module',
    // ];
}

return ArrayHelper::merge(
    require __DIR__ .'/common.php', $config,
    [
        'modules' => [
            'debug' => new UnsetArrayValue()
        ]
    ]);
