<?php

namespace app\core\auth\session;

use Yii;
use yii\rest\Controller;

class Route extends Controller
{
    public function actions()
    {
        $actions = parent::actions();
        $req = Yii::$app->request;

        if (isset($req->bodyParams['username'])) {
            $actions['create']['class'] = Create::class;
        } else {
            $actions['create']['class'] = View::class;
        }

        return $actions;
    }
}