<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    'RedisNative' => [
        'host' => 'localhost',
        'port' => 6379,
        'dbIndex' => 4,
    ],
    'SliderTimer' => [
        'timeout' => 1800, //1800 = 30 минут * 60 секунд
    ]
];
