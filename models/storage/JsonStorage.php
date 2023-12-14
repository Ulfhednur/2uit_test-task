<?php

namespace app\models\storage;

use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Class JsonStorage
 * @package app\models\storage
 *
 * @implements app\models\storage\StorageInterface
 */
class JsonStorage extends AbstractFileStorage
{
    /**
     * Имя файла в котором хранятся данные
     */
    const FILE_NAME = 'storage.json';

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'fio', 'email', 'phone'], 'required'],
            [['id'], 'string', 'max' => 32, 'min' => 32],
            [['fio'], 'string', 'max' => 1000],
            [['email'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 15],
        ];
    }

    /**
     * @return array
     */
    protected function readFromFile(): array
    {
        if (empty($this->items)) {

            /** @var \yii2tech\filestorage\local\Bucket $bucket */
            $bucket = Yii::$app->fileStorage->getBucket('itemsFiles');
            if ($bucket->fileExists(self::FILE_NAME)) {
                $this->items = Json::decode($bucket->getFileContent(self::FILE_NAME), true) ?? [];
            } else {
                $this->items = [];
            }
        }
        return $this->items;
    }

    public function save(bool $runValidation = true): bool
    {
        if ($runValidation) {
           if (!$this->validate()) {
               return false;
           } 
        }
        $this->items[$this->id] = $this->toArray(['fio', 'email', 'phone']);

        /** @var \yii2tech\filestorage\local\Bucket $bucket */
        $bucket = Yii::$app->fileStorage->getBucket('itemsFiles');
        $bucket->saveFileContent(self::FILE_NAME, Json::encode($this->items));
        return true;
    }
}