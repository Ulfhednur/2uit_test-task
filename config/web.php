<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'timeZone' => 'Europe/Moscow',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'hJgCBMxIog1iGOtiJowSO_4VFaxA3dDO',
            'parsers' => [
                'application/json' => yii\web\JsonParser::class,
            ]
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => app\models\Identity::class,
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'fileStorage' => [
            'class' => yii2tech\filestorage\local\Storage::class,
            'basePath' => '@FileStorage',
            'baseUrl' => '@web/files',
            'dirPermission' => 0775,
            'filePermission' => 0755,
            'buckets' => [
                'itemsFiles' => [
                    'baseSubPath' => 'items',
                    'fileSubDirTemplate' => '{^name}/{^^name}',
                ],
            ]
        ],
        'db' => $db,
        'jwt' => [
            'class' => \kaabar\jwt\Jwt::class,
            'key' => 'U}MpK|~WBNd4iDCDCZOXlppS%xcbZV65apbC$GR27TOwsvU57FP*UYK}By3Qrz34',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['auth' => 'auth'],
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'OPTIONS' => 'options',
                        'OPTIONS login' => 'options',
                        'OPTIONS refresh-token' => 'options',
                        '' => 'options',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => ['items' => 'items'],
                    'extraPatterns' => [
                        'PUT,PATCH' => 'update',
                        'POST' => 'create',
                        'GET,HEAD ' => 'index',
                        'OPTIONS' => 'options',
                        '' => 'options',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
