<?php


const MB = 1024 * 1024;
const GB = 1024 * MB;
const SECS = 1;
const MINUTES = 60 * SECS;
const HOURS = 60 * MINUTES;
const DAYS = 24 * HOURS;
const WEEKS = 7 * DAYS;


if (YII_ENV_DEV) {
    yii\base\Event::on('yii\base\Model', 'afterValidate', function ($e) {
        $e->sender->errors && Yii::warning(['errors' => $e->sender->errors, 'attrs' => $e->sender->attributes], $e->sender->formName() . '::EVENT_AFTER_VALIDATE');
    });

    Yii::debug('APP VERSION => ' . print_r(latestCommit(), 1));
}
