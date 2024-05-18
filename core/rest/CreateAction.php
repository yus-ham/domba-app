<?php

namespace app\core\rest;

use Yii;
use yii\helpers\Url;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\web\ServerErrorHttpException;

class CreateAction extends \yii\rest\CreateAction
{
    use UploadedFileLoader;

    public function init()
    {
        $this->modelClass = StringHelper::dirname(static::class) .'\\'. Inflector::classify($this->controller->id);
        parent::init();
    }

    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /** @var \yii\db\ActiveRecord */
        $model = new $this->modelClass();

        $this->loadFiles($model);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $this->beforeValidate($model);

        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', $model->getPrimaryKey(true));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        }
        elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    protected function beforeValidate($model)
    {
    }
}