<?php

require dirname(__DIR__) .'/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3))->load();

define('YII_DEBUG', in_array($_ENV['APP_ENV'] ?? '', ['dev','development']));
define('YII_ENV', YII_DEBUG ? 'dev' : 'prod');

require dirname(__DIR__) .'/vendor/yiisoft/yii2/Yii.php';

$config = require dirname(__DIR__) .'/core/config/web.php';

$app = new yii\web\Application($config);
$app->run();