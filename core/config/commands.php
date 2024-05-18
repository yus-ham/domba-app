<?php

return [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationTable' => '00_migration',
    ],
    'routes' => 'app\core\Router',
    'token' => 'app\core\auth\TokenUtil',
];