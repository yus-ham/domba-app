<?php


$rules = [
    'artikel' => [],
    'error' => [],
    'session' => [],
];

if (!Yii::$app->controller) {
    foreach ($rules as $route => $rule) {
        $rules[$route] = [
            'class' => 'yii\rest\UrlRule',
            'pluralize' => false,
            'controller' => $route,
            'extraPatterns' => $rule,
        ];
    }
}

return $rules;
