<?php
namespace app\controllers;

use app\models\ItemForm;
use app\models\storage\CacheStorage;
use app\models\storage\DBStorage;
use app\models\storage\JsonStorage;
use app\models\storage\XlsxStorage;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Class ItemsController
 * @package app\modules\catalog\controllers
 */
class ItemsController extends ApiAbstractController
{
    public $modelClass = ItemForm::class;

    /**
     * Добавляем исключения в авторизацию
     *
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'][] = 'index';
        return $behaviors;
    }

    /**
     * Вывод всех записей хранилища
     *
     * @param $storage
     * @return array
     * @throws UnprocessableEntityHttpException
     */
    public function actionIndex()
    {
        $storage = \Yii::$app->getRequest()->get('storage');
        $storageClass = ItemForm::getStorageNames()[$storage];
        if ($storageClass) {
            /** @var DBStorage|XlsxStorage|CacheStorage|JsonStorage $storage */
            $storage = new $storageClass();
            return $storage->getItems();
        }
        throw new UnprocessableEntityHttpException('Недопустимый тип хранилища');
    }

    /**
     * Создание записи
     * @return string[]
     * @throws NotFoundHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionCreate()
    {
        /** @var ItemForm $model */
        $model = \Yii::$container->get($this->modelClass);
        $model->scenario = ItemForm::SCENARIO_CREATE;

        return $this->save($model);
    }

    /**
     * Обновление записи
     *
     * @return string[]
     * @throws NotFoundHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionUpdate()
    {
        /** @var ItemForm $model */
        $model = \Yii::$container->get($this->modelClass);
        $model->scenario = ItemForm::SCENARIO_UPDATE;

        return $this->save($model);
    }

    /**
     * Выполняет сохранение
     *
     * @param ItemForm $model
     * @return array
     * @throws NotFoundHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function save(ItemForm $model): array
    {
        $input = \Yii::$app->getRequest()->getBodyParams();
        if ($model->load($input)) {
            if($model->save()) {
                return ['status' => 'ok'];
            }
            $response = $this->response;
            $response->setStatusCode(422);
            return $model->getErrorSummary(true);
        }
        throw new NotFoundHttpException('Запись не найдена', 404);
    }
}
