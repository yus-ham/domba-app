<?php

namespace app\core\rest;

use yii\base\ActionEvent;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\Inflector;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\rest\UrlRule;
use yii\base\InlineAction;
use yii\base\Behavior;
use Yii;

class Bootstrap extends Behavior
{
    public $cors;
    public $pagination;
    public $showErrorTrace;

    public function events()
    {
        return [
            'beforeRequest' => function () {
                $this->handleCors();
                $this->setupRest();
                // $this->registerUrlRule();
            },
            'beforeAction' => function (ActionEvent $event) {
                Yii::debug('RestSetup::events#beforeAction', __METHOD__);
                $this->userHasAccess($event);
                $this->setPagination($event->action);
                $this->setResponseSerializer($event->action);
            },
        ];
    }

    protected function handleCors()
    {
        $req = Yii::$app->request;
        if (!$req->origin or $req->origin === $req->hostInfo) {
            return;
        }

        $filter = new Cors();

        foreach ((array) $this->cors as $key => $value) {
            $filter->cors[$key] = $value;
        }

        $filter->cors['Access-Control-Expose-Headers'][] = 'X-Pagination-Per-Page';
        $filter->cors['Access-Control-Expose-Headers'][] = 'X-Pagination-Total-Count';

        $dummyAction = (object)['id' => 0];
        if (!$filter->beforeAction($dummyAction)) {
            Yii::$app->response->format = 'json';
            Yii::$app->end();
        }
    }

    protected function setupRest()
    {
        Yii::$app->request->parsers['application/json'] = 'yii\web\JsonParser';
        Yii::$app->request->parsers['multipart/form-data'] = 'yii\web\MultipartFormDataParser';
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->on('beforeSend', [$this, 'formatResponse']);
        Yii::$app->user->loginUrl = null;
        Yii::$app->user->enableSession = false;
        Yii::$app->urlManager->enablePrettyUrl = true;
    }
    
    protected function registerUrlRule()
    {
        $routes = require dirname(__DIR__) .'/config/routes.php';

        if ($routes[Yii::$app->request->pathInfo] ??null) {
            return;
        }

        $controllerMap = Yii::$app->controllerMap;

        if ($controllerMap[Yii::$app->request->pathInfo] ??null) {
            return;
        }

        $pathInfo = dirname(Yii::$app->request->pathInfo);
        $pattern = basename(Yii::$app->request->pathInfo);
        foreach ($routes[$pathInfo]['extraPatterns'] ?? [] as $rule => $action) {
            if (str_ends_with($rule, " $pattern")) {
                return;
            }
        }

        $namespace = \yii\helpers\StringHelper::dirname(__NAMESPACE__);
        @list($module, $controller) = explode('/', Yii::$app->request->pathInfo);

        if (empty($module)) {
            $route = Yii::$app->defaultRoute;
            $prefix = $namespace . '\\' . $route;
        } else {
            if (empty($controller)) {
                $controller = 'default';
            }
            $route = "$module/$controller";
            $prefix = $namespace . '\\domains\\' . $module;
        }

        $controllerClass = $prefix . '\\controllers\\' . Inflector::camelize($controller) . 'Controller';

        $restRule = [
            'class' => UrlRule::class,
            'pluralize' => false,
            'controller' => $route,
        ];

        if ($this->addUrlRule($restRule, $controllerClass)) {
            return;
        }

        $route = rtrim(Yii::$app->request->pathInfo, '/');
        Yii::$app->request->pathInfo = $route;
        $prefix = $namespace . '\\controllers\\';

        do {
            $restRule['controller'] = $route;
            $namespace = $prefix;

            if ($ns = trim(dirname($route), '.')) {
                $namespace .= str_replace('/', '\\', $ns) . '\\';
            }

            $controllerClass = $namespace . Inflector::camelize(basename($route)) . 'Controller';
            if ($this->addUrlRule($restRule, $controllerClass)) {
                return;
            }
        } while ($route = trim(dirname($route), '.'));
    }

    protected function addUrlRule($rule, $controller)
    {
        Yii::debug('Test controller: '. $controller, __METHOD__);

        if (!class_exists($controller)) {
            return;
        }

        $class = new \ReflectionClass($controller);

        foreach ($class->getMethods() as $method) {
            $params = $method->getParameters();

            foreach ($method->getAttributes() as $attr) {
                if (!str_ends_with($attr->getName(), 'Route')) {
                    continue;
                }

                @list($new_rule, $action) = $attr->getArguments();

                if ($method->getName() === 'actions');
                elseif (!str_starts_with($method->getName(), 'action')) {
                    continue;
                }
                else {
                    $action = Inflector::camel2id(str_replace('action', '', $method->getName()));
                }

                $rule['extraPatterns'][$new_rule] = $action;
                Yii::debug("Rule added: $new_rule => $rule[controller]#$action", __METHOD__);
            }
        }

        return !Yii::$app->urlManager->addRules([$rule]);
    }

    protected function userHasAccess($event)
    {
        if (!$event->isValid or !Yii::$app->controller instanceof Controller) {
            return;
        }

        $action = $event->action;
        $actionRef = new \ReflectionObject($action);

        foreach ($actionRef->getAttributes() as $attr) {
            if (str_ends_with($attr->getName(), '\SkipAuth')) {
                return true;
            }
        }

        if ($actions = Yii::$app->controller->skipAuth ?? null) {
            if ($actions === true or $actions === '*') {
                return true;
            }

            if (is_string($actions)) {
                $actions = preg_split('/\s*(,|\|)\s*/', $actions);
            }

            if (in_array($action->id, $actions)) {
                return true;
            }
        }

        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod(Yii::$app->controller, 'action' . Inflector::classify($action->id));
            foreach ($method->getAttributes() as $attr) {
                if (str_ends_with($attr->getName(), 'SkipAuth')) {
                    return;
                }
            }
        }

        if (!(new HttpBearerAuth)->beforeAction($action)) {
            return;
        }

        return true;

        // return (new Access(function($action, $user) {
        //     $routes = require(Yii::getAlias('@api') .'/../config/routes-auth.php');
        //     $pathInfo = Yii::$app->request->pathInfo;
        //     $privs = $user->privileges;

        //     $actions = [
        //         'view' => 'read',
        //         'create' => 'insert',
        //         'update' => 'update',
        //         'delete' => 'remove',
        //         'index' => 'read',
        //     ];

        //     $access = $actions[$action->id] ??null;

        //     Yii::warning($pathInfo, __METHOD__);


        //     foreach ($routes as $pattern => $route) {
        //         if (preg_match("#^$pattern$#", $pathInfo)
        //         && isset($route['priv'])) { //@FIXME: buang jika mapping sudah selesai
        //             if (checkAccessByParameters($actions, $route) === false) {
        //                 return false;
        //             }
        //             return userHasPriv($route['priv'], $access ?: 'read');
        //         }
        //     }

        //     return true; //@FIXME: buang jika mapping sudah selesai

        // }))->beforeAction($action);
    }

    protected function setPagination($action)
    {
        if ($action->id === 'index') {
            $config = ArrayHelper::merge(['class' => Pagination::class], $this->pagination ?: []);
            Yii::$container->set('yii\data\Pagination', $config);
        }
    }

    protected function setResponseSerializer($action)
    {
        if (property_exists($action->controller, 'serializer')) {
            $action->controller->serializer = __NAMESPACE__ .'\Serializer';
        }
    }

    public function formatResponse(yii\base\Event $ev)
    {
        if (Yii::$app->controller && Yii::$app->controller->module->id === 'debug') {
            return;
        }

        Yii::$app->response->format = 'json';
        $ex = Yii::$app->errorHandler->exception;

        if ($ex) {
            if ($ex instanceof \Firebase\JWT\ExpiredException) {
                Yii::$app->response->statusCode = 401;
            }

            Yii::$app->response->data = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
            ];

            $this->showErrorTrace && Yii::$app->response->data['trace'] = $ex->getTrace();
        }
    }
}

function checkAccessByParameters($actions, $route)
{
    foreach($actions as $access) {
        if (isset($route[$access]['params'])) {
            $input_params = Yii::$app->request->bodyParams;

            foreach ($route[$access]['params'] as $name => $value) {
                if (isset($input_params[$name]) && $input_params[$name] == $value) {
                    return userHasPriv($route[$access]['priv'], $access);
                }
            }
        }
    }
}