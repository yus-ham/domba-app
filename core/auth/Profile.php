<?php

namespace app\core\auth;

use Yii;


class Profile extends Identity
{
    /** @var \yii\web\UploadedFile */
    public $photo_file;

    public function rules()
    {
        $rules = parent::rules();
        unset($rules['gids']);

        return array_merge($rules, [
            [['phone','mobilephone','city','state','address'], 'string'],
            [['zipcode'], 'integer'],
            ['photo_file', 'file', 'extensions'=> 'jpg,jpeg,png,webp'],
        ]);
    }

    public function fields()
    {
        return [
            'id', 'name', 'username', 'email',
            'status', 'zipcode', 'address', 'state',
            'city', 'phone', 'mobilephone',
        ];
    }
}