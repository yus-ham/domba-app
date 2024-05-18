<?php

namespace app\core\auth\session;

use app\core\rest\CreateAction;
use Yii;


#[SkipAuth]
class Create extends CreateAction
{
    public function run()
    {
        $req = Yii::$app->request;

        $session = new Session();
        $session->load($req->bodyParams, '');

        if ($session->authenticate()) {
            Yii::$app->response->statusCode = 201;
        }

        return $session;
    }
}