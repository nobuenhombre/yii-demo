<?php

use \common\services\RedisNative;

$c = Yii::$container;

$c->setSingleton(
    RedisNative::class,
    RedisNative::class,
    [
        Yii::$app->params['RedisNative']['host'] ?? '',
        Yii::$app->params['RedisNative']['port'] ?? 0,
        Yii::$app->params['RedisNative']['dbIndex'] ?? 0,
    ],
);