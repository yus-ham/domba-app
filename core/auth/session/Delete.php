<?php

namespace app\core\auth\session;

use Yii;

#[SkipAuth]
class Delete extends \yii\base\Action
{
    public function run($id = null)
    {
        // #[\Route('DELETE')]
        //     if ($user = Yii::$app->user->identity) {
        //         try {
        //             SsoSession::revokeToken($user);
        //         } catch (\Throwable $throwable) {
                    
        //         }
        //     }
    
        //     $cookie = Yii::createObject([
        //         'class' => 'yii\web\Cookie',
        //         'name' => 'rt',
        //         'path' => Yii::$app->params['rtCookiePath'] ?? (Yii::$app->request->baseUrl ?: '/'),
        //     ]);
        //     Yii::$app->response->cookies->remove($cookie);
        //     Yii::$app->response->statusCode = 204;
    }
}