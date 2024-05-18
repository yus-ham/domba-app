<?php
namespace app\core\rest;

class Serializer extends \yii\rest\Serializer
{
    protected function serializeModelErrors($model)
    {
        $result = [];
        $this->response->setStatusCode(422, 'Data Validation Failed.');

        foreach ($model->getErrors() as $field => $messages) {
            $result[$field] = (count($messages) > 1 OR !is_numeric(key($messages))) ? $messages : $messages[0];
        }

        return $result;
    }
}