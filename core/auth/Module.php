<?php

namespace app\core\auth;

use Yii;

/**
 * @property int $id
 * @property string $name
 */
class Module extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'ap_module';
    }

    public function rules()
    {
        return [
            [['name','status'], 'safe'],
        ];
    }

    public function getRoleModules()
    {
        return $this->hasMany(RoleModule::class, ['module' => 'id']);
    }
}
