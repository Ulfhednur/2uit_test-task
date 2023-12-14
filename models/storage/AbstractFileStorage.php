<?php

namespace app\models\storage;

use yii\base\Model;

/**
 * Class AbstractFileStorage
 * @package app\models\storage
 *
 * @implements app\models\storage\StorageInterface
 */
abstract class AbstractFileStorage extends Model implements StorageInterface
{
    /**
     * Массив идентификаторов (md5-хэш ФИО) всех записей хранилища
     * @var array
     */
    protected array $items = [];

    /**
     * Уникальный идентификатор записи. md5-хэш ФИО.
     * @var string
     */
    public string $id;

    /**
     * ФИО
     *
     * @var string
     */
    public string $fio;

    /**
     * e-mail
     *
     * @var string
     */
    public string $email;

    /**
     * Телефон
     *
     * @var string
     */
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
    public function getStorageName(): string
    {
        return get_class($this);
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

    /**
     * @return array
     */
    protected abstract function readFromFile(): array;

    /**
     * {@inheritdoc}
     */
    public abstract function save(bool $runValidation = true): bool;

    /**
     * {@inheritdoc}
     */
    public static function loadData($data): self
    {
        $instance = new static();
        $instance->load($data);
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsIndex(): array
    {
        $items = $this->getItemsRaw();
        $itemsIndex = array_keys($items);
        return $itemsIndex ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $id): bool
    {
        return in_array($id, $this->getItemsIndex());
    }

    /**
     * Возвращает записи с идентификаторами в формате key => value
     * @return array
     */
    protected function getItemsRaw(): array
    {
        if (empty($this->items)) {
            return $this->readFromFile();
        }
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return array_values($this->getItemsRaw());
    }
}