<?php

namespace app\core\auth;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessRule;
use yii\filters\AccessControl;
use yii\web\UnauthorizedHttpException;

/**
 * Usage in controller:
 *
 *   public function behaviors()
 *   {
 *       return [
 *           new Access(), // default access for logged in user
 *           new Access(['user_role_id' => 1]), // for admin only
 *           new Access(['user_role_id' => [1,2]]), // for role 1 or 2
 *           new Access(['! user_role_id' => [1,2]]), // for except role 1 or 2
 *           new Access(fn($user) => return Yii::$app->user->user_role_id == 1), // php7.4
 *       ];
 *   }
 */

class Access extends AccessControl
{
    private $roleAttr;

    public function __construct($config = [])
    {
        $this->roleAttr = ArrayHelper::remove($config, 'roleAttr', 'role_id');

        parent::__construct($config);
        $rule = ['allow' => true];

        if (is_callable($config)) {
            $rule['matchCallback'] = function ($rule, $action) use ($config) {
                return $config($action, Yii::$app->user->identity);
            };
        } elseif ($role = $this->getRoleConfig($config)) {
            $rule['matchCallback'] = $this->roleMatcher($role, (array)$role['ids']);
            $config && Yii::configure($this, $config);
        } else {
            $rule['roles'] = ['@'];
        }

        $this->init();
        $this->rules[] = new AccessRule($rule);
    }

    protected function getRoleConfig(&$config)
    {
        foreach ($config as $key => $v) {
            if ($key === $this->roleAttr) {
                unset($config[$key]);
                return ['neg' => false, 'ids' => $v];
            } elseif (preg_match('/^!\s*'.$this->roleAttr.'$/', $key)) {
                unset($config[$key]);
                return ['neg' => true, 'ids' => $v];
            }
        }
    }

    protected function roleMatcher($role, $roleIds)
    {
        return function () use($role, $roleIds) {
            if (Yii::$app->user->identity) {
                $match = in_array(Yii::$app->user->identity[$this->roleAttr], $roleIds);
                return $role['neg'] ? !$match : $match;
            }
        };
    }

    protected function denyAccess($user)
    {
        if ($user->isGuest ??null and Yii::$app->request->isAjax) {
            throw new UnauthorizedHttpException();
        }
        parent::denyAccess($user);
    }
}