<?php

namespace app\core\auth;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

/**
 * @property Token $token
 * @property Role[] $roles
 * @property RoleModule[] $privileges
 * @property string[]|string $id
 * @property string $username
 * @property string $authkey
 */
class Identity extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;

    public $new_password;
    public $role_ids;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ap_user';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($type === 'RefreshToken') {
            return self::find()
                ->innerJoinWith('refreshToken')
                ->where([
                    'value' => $token,
                    'status' => self::STATUS_ACTIVE,
                ])->one();
        }

        $jwt = Token::decode($token);

        return static::find()
            ->joinWith('token t')
            ->where([
                'status' => self::STATUS_ACTIVE,
                'id' => $jwt->uid,
            ])->one();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    public function rules()
    {
        return [
            ['email', 'email'],
            ['username', 'unique', 'message' => 'User ID ini sudah digunakan.'],
            ['email', 'unique', 'message' => 'Alamat Email ini sudah digunakan.'],
            ['role_ids', 'each', 'role' => ['integer']],
            [['username', 'name', 'email'], 'required'],
            [['name','username'], 'string', 'min' => 3],
            [['new_password'], 'string', 'min' => 5],
            'role_ids' => ['role_ids', 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return;
        }

        if ($this->new_password) {
            $this->setPassword($this->new_password);
        }

        if (!$this->role_ids) {
            $this->addError('role_ids', 'User role tidak boleh kosong.');
            return false;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->role_ids) {
            $roles = array_map(fn($gid) => [$this->id, $gid], $this->role_ids);
            self::getDb()->createCommand()->delete('ap_user_role', ['user' => $this->id])->execute();
            self::getDb()->createCommand()->batchInsert('ap_user_role', ['user','role'], $roles)->execute();
        }
    }

    public function transactions()
    {
        return [
            'default' => self::OP_INSERT|self::OP_UPDATE,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'role_ids' => 'User Role',
            'username' => 'User ID',
            'name' => 'Nama',
            'new_password' => 'Kata Sandi',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash("$password");
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'email',
            'status',
            'username',
            'role_ids' => fn() => array_map(fn($x) => $x->id, $this->roles),
            // 'avatar' => fn() => '2024-05-14_16-53.png',
        ];
    }

    public function getRoles()
    {
        return $this->hasMany(Role::class, ['id' => 'role'])->viaTable('ap_user_role', ['user' => 'id']);
    }

    public function getToken()
    {
        return $this->hasOne(Token::class, ['user_id' => 'id']);
    }

    public function getPrivileges()
    {
        return RoleModule::find()
            ->innerJoinWith(['role'])
            ->innerJoin('ap_user_role ur', 'ur.role = ap_role.id')
            ->andWhere(['ur.user' => $this->id])
            ->select('ap_role_module.*, ap_role.name role_name')
            ->all();
    }
}