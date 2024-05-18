<?php

namespace app\core;

use yii\base\Component;

class Event extends \yii\base\Event
{
    public $detail;

    public function __construct($detail = null, $config = [])
    {
        $this->detail = $detail === null ? null : (object)$detail;
        parent::__construct($config);
    }

    public static function trigger($class, $name, $event = null)
    {
        if (!$event) {
            $event = new Event($event);
        }

        parent::trigger($class, $name, $event);
    }

    public static function dispatch(Component $component, $name, $event = null)
    {
        if (!$event) {
            $event = new Event($event);
        }

        $component->trigger($name, $event);

        return $event;
    }
}