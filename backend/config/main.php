<?php

use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning', 'info', 'trace', 'profile'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['user', 'login-history'],
                    'prefix' => 'api'
                ],
                'POST api/auth/login' => 'auth/login',
                'POST api/auth/register' => 'auth/register',
                'POST api/auth/verify' => 'auth/verify',
                'GET api/user/get-qr-code' => 'user/get-qr-code',
                'POST api/user/confirm-2fa' => 'user/update-two-fa',
                'GET api/user/sendCodeEmail' => 'user/send-code-email',
                'GET api/user/get2FAMethod' => 'user/get-twofa-method',
                'POST api/user/verify-code-unlock' => 'user/verify-code-unlock',
                'POST api/user/update-code-unlock' => 'user/update-code-unlock',
                'POST api/user/update-new-password' => 'user/update-new-password',
            ],
        ],
        "tfa" => new TwoFactorAuth(new EndroidQrCodeProvider()),
    ],
    'params' => $params,
];
