<?php

namespace app\core;

use yii\base\Controller;

class ErrorHandler extends Controller
{
    public function actions()
    {
        return [
            'handle' => ['class' => 'yii\web\ErrorAction', 'layout' => false],
        ];
    }
}