<?php

use \yii\di\Instance;
use \common\services\RedisNativeService;
use \common\services\slider\common\SliderKeyService;
use \common\services\slider\common\SliderProgressService;
use \common\services\slider\common\SliderStatusService;
use \common\services\slider\common\SliderMessagesService;
use \common\services\slider\common\SliderImagesService;
use \common\services\slider\common\SliderTimerService;
use \common\services\slider\SliderService;
use \common\services\slider\SourceService;

$c = Yii::$container;

$c->setSingletons([

    RedisNativeService::class => [
        'class' => RedisNativeService::class,
        '__construct()' => [
            Yii::$app->params['RedisNative']['host'] ?? '',
            Yii::$app->params['RedisNative']['port'] ?? 0,
            Yii::$app->params['RedisNative']['dbIndex'] ?? 0,
        ],
    ],

    SliderKeyService::class => [
        'class' => SliderKeyService::class,
    ],

    SliderProgressService::class => [
        'class' => SliderProgressService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
        ]
    ],

    SliderStatusService::class => [
        'class' => SliderStatusService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
        ]
    ],

    SliderMessagesService::class => [
        'class' => SliderMessagesService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
        ]
    ],

    SliderImagesService::class => [
        'class' => SliderImagesService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
        ]
    ],

    SliderTimerService::class => [
        'class' => SliderTimerService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
            Yii::$app->params['SliderTimer']['timeout'] ?? 1800, //1800 = 30 минут * 60 секунд
        ]
    ],

    SourceService::class => [
        'class' => SourceService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
        ]
    ],

    SliderService::class => [
        'class' => SliderService::class,
        '__construct()' => [
            Instance::of(RedisNativeService::class),
            Instance::of(SliderKeyService::class),
            Instance::of(SliderImagesService::class),
            Instance::of(SourceService::class),
        ]
    ],

]);
