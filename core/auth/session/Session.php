<?php
namespace app\core\auth\session;

use app\core\auth\Identity;
use app\core\auth\Token;
use yii\base\Model;
use Yii;

class Session extends Model
{
    public $id;
    public $username;
    public $password;
    public $rememberMe = true;
    private $identity = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['rememberMe', 'boolean'],
            ['password', 'required'],
        ];
    }

    public function loadFromToken($token)
    {
        $identity = Identity::find()
            ->innerJoinWith('token')
            ->where([
                'value' => $token,
                'status' => Identity::STATUS_ACTIVE,
            ])->one();

        if ($identity) {
            return $this->identity = $identity;
        }
    
        $this->addError('token', 'Invalid token');
    }

    public function isActive()
    {
        return $this->identity;
    }

    public function authenticate()
    {
        if (!$this->hasValidCredentials()) {
            return;
        }

        $cookie = Yii::createObject([
            'class' => 'yii\web\Cookie',
            'value' => $this->generateRefreshToken(),
            'name' => 'rt',
        ]);
        Yii::$app->response->cookies->add($cookie);
    
        return true;
    }

    protected function hasValidCredentials()
    {
        $identity = $this->getIdentity();

        if (!$identity) {
            return $this->addError('username', 'Username tidak ditemukan.');
        }

        // $2b$ is prefix created by nodejs bcrypt library
        $hash = str_replace('$2b$', '$2y$', $identity->password_hash);

        if (!Yii::$app->security->validatePassword($this->password, $hash)) {
            return $this->addError('password', 'Password salah.');
        }

        return true;
    }

    protected function setIdentityFromToken()
    {
        $this->identity = Identity::find()
            ->innerJoinWith('token')
            ->where([
                'value' => $this->id,
                'status' => Identity::STATUS_ACTIVE,
            ])->one();
    }

    public function validateToken()
    {
        $this->setIdentityFromToken();

        if (!$this->identity) {
            return $this->addError('token', 'Invalid token');
        }

        // if ($this->identity->refreshToken->expired_at < time()) {
        //     return $this->addError('token', 'Token Expired');
        // }

        // $this->_rt = $this->identity->refreshToken;
    }

    public function getIdentity()
    {
        if ($this->identity === false) {
            $this->identity = Identity::find()
                ->where(['username' => $this->username])
                ->andWhere(['status' => Identity::STATUS_ACTIVE])
                ->one();
        }
        return $this->identity;
    }

    public function fields()
    {
        return [
            'identity' => fn() => $this->identity,
            'access_token' => fn() => $this->createAccessToken(),
            'privileges' => fn() => $this->identity->privileges,
        ];
    }

    protected function createAccessToken()
    {
        if ($this->identity) {
            return Token::encode($this->identity->id);
        }
    }

    protected function generateRefreshToken()
    {
        $data = [
            'user_ip' => Yii::$app->request->userIP,
            'user_id' => $this->identity->id ?? 0,
        ];

        $token = Token::findOne($data) ?: new Token($data);
        $token->expired_at = strtotime('3 day');
        $token->value = Yii::$app->security->generateRandomString();

        return $token->save(false) ? $token->value : null;
    }
}