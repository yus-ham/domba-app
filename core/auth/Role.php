<?php

namespace app\core\auth;

use Yii;

/**
 * @property int $id
 * @property string $name
 */
class Role extends \yii\db\ActiveRecord
{
    const PEGAWAI = 'Pegawai';

    public $privs = [];

    public static function tableName()
    {
        return 'ap_role';
    }

    public function rules()
    {
        return [
            [['name','status','privs'], 'safe'],
        ];
    }

    public function afterSave($insert, $changedAttrs)
    {
        $this->saveRoleModules();
        parent::afterSave($insert, $changedAttrs);
    }

    public function saveRoleModules()
    {
        $modules = Module::find()->all();
            
        foreach ($this->privs as $module_id => $privs) {
            $module = arrayFind($modules, fn($module) => $module->id === $module_id);

            if ($module && $module->status) {
                $data[] = [
                    $this->id, $module_id,
                    ($module->read && isset($privs['read']) && $privs['read'] == 1) ? 1 : 0,
                    ($module->insert && isset($privs['insert']) && $privs['insert'] == 1) ? 1 : 0,
                    ($module->update && isset($privs['update']) && $privs['update'] == 1) ? 1 : 0,
                    ($module->remove && isset($privs['remove']) && $privs['remove'] == 1) ? 1 : 0,
                ];
            }
        }

        if (!empty($data)) {
            RoleModule::deleteAll(['role' => $this->id]);
            self::getDb()->createCommand()->batchInsert(RoleModule::tableName(), ['role','module','read','insert','update','remove'], $data)->execute();
        }
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'status',
        ];
    }
}
