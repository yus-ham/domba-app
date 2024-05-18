<?php

namespace app\core\rest;

use yii\web\UploadedFile;
use Yii;

trait UploadedFileLoader
{
    /**
     * @param \yii\base\Model $model
     * @return \yii\base\Model
     */
    public function loadFiles($model)
    {
        $data = Yii::$app->request->bodyParams;

        foreach ($_FILES as $attr => $file) {
            if (is_array($file['name'])) {
                $files = UploadedFile::getInstancesByName($attr);
                unset($data[$attr]);
                $i = 0;

                foreach ($file['size'] as $key => $size) {
                    if (!$size) {
                        continue;
                    }
                    if ($files[$i]->type === 'blob') {
                        $parts = explode('/', $files[$i]->type);
                        $files[$i]->name = $name .'.'. array_pop($parts);
                    }
                    $model->$attr[$key] = $files[$i];
                    $i++;
                }
            }
            elseif (!$file['error'] && $file['size']) {
                $model->$attr = UploadedFile::getInstanceByName($attr);
                unset($data[$attr]);
                if ($model->$attr->name === 'blob') {
                    $parts = explode('/', $model->$attr->type);
                    $model->$attr->name = $attr .'.'. array_pop($parts);
                }
            }
        }

        return Yii::$app->request->setBodyParams($data) ?: $model;
    }
}