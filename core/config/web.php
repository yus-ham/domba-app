<?php

use yii\helpers\ArrayHelper;

require __DIR__ . '/bootstrap.php';
$params = require __DIR__ . '/params.php';
$baseUrl = str_replace('/web', '', (new \yii\web\Request)->getBaseUrl());

$config = [
    'id' => 'basic',
    'name' => 'Faktur',
    'bootstrap' => ['setting'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'setting' => [
            'class' => 'app\domains\setting\Setting',
        ],
        'request' => [
            'enableCsrfCookie' => false,
            'enableCookieValidation' => false,
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
        'session' => [
            'cookieParams' => ['path' => $baseUrl ?: '/', 'httpOnly' => true]
        ],
        'user' => [
            'identityClass' => 'app\core\auth\Identity',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity',
                'path' => $baseUrl ?: '/',
            ]
        ],
        // 'errorHandler' => [
        //     'errorAction' => 'error/handle',
        // ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => array_merge(require __DIR__ .'/routes-rules.php', [
            ]),
        ],
        'formatter' => [
            'dateFormat' => 'dd/MM/yyyy',
            'datetimeFormat' => 'dd/MM/yyyy hh:mm',
        ],
    ],
    'params' => $params,
    'as rest' => [
        'class' => 'app\core\rest\Bootstrap',
        'pagination' => ['pageSize' => intval($_GET['per-page'] ?? 10)],
        'showErrorTrace' => false,
    ],
    'controllerMap' => require __DIR__ .'/routes.php',
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}


$config = ArrayHelper::merge(require __DIR__ .'/common.php', $config);
is_file(__DIR__ .'/env.php') and require __DIR__ .'/env.php';

return $config;