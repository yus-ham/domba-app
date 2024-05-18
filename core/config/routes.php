<?php

return [
    'error' => 'app\\core\\ErrorHandler',
    'session' => 'app\\core\\auth\\session\\Route',
    'artikel' => [
        'class' => 'app\\domains\\blog\\Blog',
        'modelClass' => 'app\\domains\\blog\\Artikel',
    ],
];
