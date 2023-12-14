<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class SiteController
 * @package app\controllers
 */

class SiteController extends Controller
{
    public function actionError()
    {
        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            $exception = new NotFoundHttpException(Yii::t('yii', 'Page not found.'), 404);
        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
    }
}
