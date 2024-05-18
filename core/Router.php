<?php
namespace app\core;

use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\console\Controller;
use yii\db\ActiveRecordInterface;
use Yii;

class Router extends Controller
{
    private $map = [];
    private $rules = [];

    public function actionGenerate()
    {
        $domains_dir = Yii::$app->basePath . '/domains';
        $domains = new \FilesystemIterator($domains_dir, \FilesystemIterator::SKIP_DOTS);

        foreach ($domains as $domain) {
            $id = $domain->getBasename('.php');
            $this->loadRoutes("app\\domains\\$id\\" . Inflector::classify($id));
        }

        $this->rules = array_merge($this->rules, require __DIR__ .'/config/routes-rules.php', $this->rules);
        $this->map = array_merge(require __DIR__ .'/config/routes.php', $this->map);
        $this->writeToFile();

        printf("\nDone!\n");
    }

    protected function writeToFile()
    {
        printf("\nSaving configurations ...\n");

        $rules = VarDumper::export($this->rules);
        $map = VarDumper::export($this->map);

        $rules = "\n\n\$rules = $rules;\n\nif (!Yii::\$app->controller) {\n    foreach (\$rules as \$route => \$rule) {
        \$rules[\$route] = [
            'class' => 'yii\\rest\\UrlRule',
            'pluralize' => false,
            'controller' => \$route,
            'extraPatterns' => \$rule,
        ];\n    }\n}\n\nreturn \$rules;\n";

        file_put_contents(__DIR__ .'/config/routes-rules.php', "<?php\n$rules");
        file_put_contents(__DIR__ .'/config/routes.php', "<?php\n\nreturn $map;\n");
    }

    protected function loadRoutes($domain_class)
    {
        try {
            $routes = $domain_class::routes();
            printf("Loading routes from %s ...\n", $domain_class);

            foreach ($routes as $route => $class) {
                printf("$route => $class\n");
                if ($this->isEntityClass($class)) {
                    $this->map[$route] = [
                        'class' => $domain_class,
                        'modelClass' => $class,
                    ];
                }
                else {
                    $this->map[$route] = $class;
                }
                $this->rules[$route] = [];
            }
        } catch(\Throwable $t) {
        }
    }

    protected function isEntityClass($class)
    {
        $interfaces = class_implements($class);
        return in_array(ActiveRecordInterface::class, $interfaces);
    }
}