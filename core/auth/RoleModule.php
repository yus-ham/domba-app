<?php

namespace app\core\auth;


use Yii;

/**
 * @property int $id
 * @property string $name
 */
class RoleModule extends \yii\db\ActiveRecord
{
    public $role_id;
    public $role_name;

    public $_role;
    public static function tableName()
    {
        return 'ap_role_module';
    }

    public static function primaryKey()
    {
        return ['role', 'module'];
    }

    public function rules()
    {
        return [
            [['name', 'status'], 'safe'],
        ];
    }

    public function fields()
    {
        return [
            'role_id' => fn() => $this->role,
            'role' => fn() => $this->role_name,
            'module',
            'read',
            'insert',
            'update',
            'remove',
        ];
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role']);
    }
}