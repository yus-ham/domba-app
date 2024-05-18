<?php

namespace app\core\rest;

use Yii;
use yii\base\Controller;
use yii\helpers\Inflector;
use yii\rest\ActiveController;

/**
 * @property-read \yii\base\Model $search
 */
abstract class ActiveRoute extends ActiveController
{
    use UploadedFileLoader;

    public $entity;
    public $softDelete = true;

    private $_search;

    public function init()
    {
        Controller::init();
    }

    public function actions()
    {
        $actions = parent::actions();

        // if (!$this->modelClass) {
        //     $class = $this->modelClass = 'api\\models\\'. Inflector::classify($this->entity);

        //     foreach (['view','index','create','update','delete'] as $action) {
        //         $actions[$action]['modelClass'] = $class;
        //     }
        // }

        // $actions['index']['prepareDataProvider'] = fn() => $this->getSearch()->apply($_GET);

        // if ($this->softDelete) {
        //     $actions['delete']['class'] = 'api\components\SoftDeleteAction';
        // }

        $actions['create']['class'] = $this->getCreateActionClass($actions['create']['class']);

        $actions['update']['findModel'] = function($id, $action) {
            $action = clone $action;
            $action->findModel = null;
            return $this->loadFiles($action->findModel($id));
        };

        foreach ($actions as $key => $config) {
            if (method_exists($this, "action$key")) {
                unset($actions[$key]);
            }
        }

        return $actions;
    }

    protected function getCreateActionClass()
    {
        try {
            if (class_exists($actionClass = 'api\\actions\\'. $this->entity .'\\Create', true)) {
                return $actionClass;
            }
        } catch(\Throwable $th) {}

        return 'api\actions\CreateAction';
    }

    public function getSearch()
    {
        if (!$this->_search) {
            try {
                $this->_search = Yii::createObject($this->modelClass .'Search');
                $this->_search->on('beforeValidate', fn($ev) => $ev->sender->scenario = 'default');
            } catch(\Throwable $t) {
                $this->_search = Yii::createObject($this->modelClass);
            }
        }

        return $this->_search;
    }
}