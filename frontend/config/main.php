<?php

use frontend\services\LoginHistoryService;
use frontend\repositories\LoginHistoryRepository;

$container = Yii::$container;

$container->setSingleton(LoginHistoryRepository::class);
$container->setSingleton(LoginHistoryService::class, [], [
    $container->get(LoginHistoryRepository::class),
]);
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'enableCookieValidation' => true,

            'enableCsrfValidation' => true,

            'cookieValidationKey' => 'f204248f8e15b108c8e15a0b86e936270471f2ab600ded3486ed7ccd9d309a9b',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        'httpClient' => [
            'class' => yii\httpclient\Client::class,
            'baseUrl' => 'http://nginx:8080/api'
        ],
        'loginHistoryService' => [
            'class' => LoginHistoryService::class,
        ],
    ],
    'params' => $params,
];
