<?php
namespace app\core\auth;

use app\core\auth\Token;
use app\core\auth\Identity;
use Firebase\JWT\ExpiredException;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\VarDumper;
use Yii;

class TokenUtil extends Controller
{
    public $key;
    public $params = [];

    public function options($actionID)
    {
        return [
            'key',
            'params',
        ];
    }

    public function actionGenerate($identity)
    {
        $id = $identity;
        if (is_numeric($identity)) {
            $identity = Identity::findOne(['id' => $identity]);
        } else {
            $identity = Identity::findOne(['username' => $identity]);
        }

        if (!$identity) {
            throw new Exception("Identity ($id) not found\n");
            return;
        }

        if (($this->params[0] ?? null) && !is_array($this->params[0])) {
            parse_str($this->params[0], $this->params);
        }

        echo "Generating token for user '{$identity->username}' ($identity->id) ...\n";
        echo ($token = Token::encode($identity->id, $this->params, $this->key)) . "\n\n";

        return $token;
    }

    public function actionInspect($token)
    {
        echo "Inspecting token ...\n";
        
        try {
            $payload = Token::decode($token, $this->key);
        } catch(\Throwable $t) {
            try {
                $payload = $t->getPayload();

                if ($t instanceof ExpiredException) {
                    $is_expired = 'true';
                }
            } catch(\Throwable $t2) {
                throw new Exception($t->getMessage() . "\n\n");
            }
        }

        if (isset($payload)) {
            $identity = Identity::findOne(['id' => $payload->uid]);

            if ($identity) {
                $payload = [
                    'user' => $identity->toArray(['id','username']),
                    'jwt_payload' => $payload,
                ];
            }

            $payload['is_expired'] = $is_expired ?? 'false';
            VarDumper::dump($payload) . "\n\n";
        }
    }
}