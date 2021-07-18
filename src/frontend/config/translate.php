<?php

return [
    'translations' => [
        'app*' => [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => '@frontend/messages',
            'forceTranslation' => true,
            'fileMap' => [
                'app' => 'app.php',
            ],
        ]
    ]
];