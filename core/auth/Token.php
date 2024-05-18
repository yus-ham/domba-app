<?php

namespace app\core\auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use yii\helpers\ArrayHelper;
use Yii;

class Token extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'ap_token';
    }

    public function getUser()
    {
        return $this->hasOne(Identity::class, ['id' => 'user_id']);
    }

    public static function encode($user_id, $params = [], $key = null)
    {
        $token = ['uid' => $user_id];
        $exp = ArrayHelper::remove($params, 'expires');

        foreach ($params as $name => $value) {
            $token[$name] = $value;
        }

        if ($exp === null) {
            $duration = $_ENV['TOKEN_DURATION'] ?? '12 hours';
            $token['exp'] = strtotime($duration);
        }
        elseif ($exp - 0) {
            $token['exp'] = strtotime($params['exp']);
        }

        return JWT::encode($token, $key ?: $_ENV['ENCRYPT_KEY'], 'HS512');
    }

    public static function decode($token, $key = null)
    {
        return JWT::decode($token, new Key($key ?: $_ENV['ENCRYPT_KEY'], 'HS512'));
    }
}