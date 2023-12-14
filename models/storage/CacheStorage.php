<?php

namespace app\models\storage;

use Yii;
use yii\base\Model;

/**
 * Class CacheStorage
 * @package app\models\storage
 */
class CacheStorage extends Model implements StorageInterface
{
    const ITEM_INDEX_KEY = 'item_index';

    protected array $items = [];

    public string $id;
    public string $fio;
    public string $email;
    public string $phone;

    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return '';
    }

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

    protected function prepareCacheKey(string $id = null): string
    {
        if (!$id) {
            $id = $this->id;
        }
        return 'item_' . $id;
    }

    public function getItemsIndex(): array
    {
        $itemsIndex = Yii::$app->cache->get(self::ITEM_INDEX_KEY);
        return !empty($itemsIndex) ? $itemsIndex: [];
    }

    public function save(bool $runValidation = true): bool
    {
        if ($runValidation) {
           if (!$this->validate()) {
               return false;
           } 
        }
        $itemsIndex = $this->getItemsIndex();
        if (!in_array($this->id, $itemsIndex)) {
            $itemsIndex[] = $this->id;
            Yii::$app->cache->set(self::ITEM_INDEX_KEY, $itemsIndex, 0);
        }
        Yii::$app->cache->set($this->prepareCacheKey(), $this->toArray(['fio', 'email', 'phone']), 0);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function loadData($data): self
    {
        $instance = new static();
        $instance->load($data);
        return $instance;
    }

    public function hasItem(string $id): bool
    {
        return in_array($id, $this->getItemsIndex());
    }

    public function getItems(): array
    {
        if (empty($this->items)) {
            $itemsIndex = $this->getItemsIndex();
            foreach ($itemsIndex as $id) {
                $this->items[] = Yii::$app->cache->get($this->prepareCacheKey($id));
            }
        }
        return $this->items;
    }

    public function getStorageName(): string
    {
        return get_class($this);
    }
}