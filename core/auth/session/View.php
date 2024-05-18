<?php

namespace app\core\auth\session;

use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\base\Action;
use Yii;

#[SkipAuth]
class View extends Action
{
    public function run($id = null)
    {
        $req = Yii::$app->request;
        $req->enableCsrfValidation = YII_ENV_PROD;
        $clientToken = $req->headers->get('csrf');

        if (!$req->validateCsrfToken($clientToken)) {
            throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
        }

        $session = new Session();
        $session->loadFromToken($req->cookies['rt']->value ?? null);

        if ($session->isActive()) {
            return $session;
        }

        throw new UnauthorizedHttpException();
    }
}