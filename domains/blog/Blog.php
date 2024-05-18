<?php

namespace app\domains\blog;

use app\core\rest\ActiveRoute;

class Blog extends ActiveRoute
{
    public static function routes()
    {
        return [
            'artikel' => Artikel::class,
        ];
    }
}