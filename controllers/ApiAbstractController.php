<?php
namespace app\controllers;

use kaabar\jwt\JwtHttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;

/**
 * Class ApiAbstractController
 * @package app\controllers
 */
abstract class ApiAbstractController extends ActiveController
{
    public $enableCsrfValidation = false;

    /**
     * Включаем JSON как формат ответа по умолчанию
     * Отключаем сессию для соответствия стандартам RESTFull
     * @throws \yii\base\InvalidConfigException
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->enableAutoLogin = false;
    }

    /**
     * Убираем стандартные экшены
     * @return array
     * {@inheritdoc}
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['view'], $actions['update'], $actions['delete']);
        return $actions;
    }

    /**
     * Включаем JSON, как основной формат
     * Настраиваем CORS
     * Настраиваем авторизацию по JWT
     *
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ]
        ];

        $origins = \Yii::$app->params['origins'];

        if (\Yii::$app->params['breakCors']) {
            if (!empty($_SERVER['HTTP_ORIGIN']) && !in_array($_SERVER['HTTP_ORIGIN'], $origins)) {
                $origins[] = $_SERVER['HTTP_ORIGIN'];
            } elseif (!empty($_SERVER['HTTP_REFERER']) && !in_array($_SERVER['HTTP_REFERER'], $origins)) {
                $origins[] = $_SERVER['HTTP_REFERER'];
            }
        }

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => $origins,
                'Access-Control-Request-Method' => ['POST', 'PUT', 'PATCH', 'GET', 'HEAD', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'except' => [
                'options'
            ],
        ];

        return $behaviors;
    }
}