<?php
namespace app\core;

use yii\helpers\ArrayHelper;

trait Listable
{
    /** @param ActiveQuery|callable $query */
    /** @return array|callable */
    public static function optionList($query = null)
    {
        if (is_callable($query)) {
            $query = $query(self::find());
        }  elseif (!$query) {
            $query = self::find();
        }

        $map = self::listableMap($query);

        foreach ($map as $key => $value) {
            if (is_string($value)) {
                $query->addSelect($key)->addSelect($value);
            }

            return ArrayHelper::map($query->asArray()->all(), $key, $value);
        }
    }
}