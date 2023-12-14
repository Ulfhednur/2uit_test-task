<?php

namespace app\models\storage;

use yii\db\ActiveRecord;

/**
 * Class DBStorage
 * @package app\models\storage
 *
 * @implements app\models\storage\StorageInterface
 */
class DBStorage extends ActiveRecord implements StorageInterface
{
    /**
     * Массив записей хранилища
     * @var array
     */
    protected array $items = [];

    /**
     * Массив идентификаторов (md5-хэш ФИО) всех записей хранилища
     * @var array
     */
    protected array $itemsIndex = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'items';
    }

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

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $id): bool
    {
        return self::_hasItem($id);
    }

    /**
     * Осуществляет непосредственно проверку наличия записи
     *
     * @param string $id
     * @return bool
     */
    protected static function _hasItem(string $id): bool
    {
        return self::find()
            ->where(['id' => $id])
            ->exists();
    }

    /**
     * А тут мы устраняем конфликт между родителем и интерфейсом
     * Хочу нормальное мультинаследование а не трейты :wall:
     *
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * {@inheritdoc}
     */
    public static function loadData($data): self
    {
        if(self::_hasItem($data['id'])) {
            $instance = self::findOne(['id' => $data['id']]);

            foreach (['id', 'fio', 'email', 'phone'] as $attribute) {
                if (in_array($attribute, array_keys($instance->attributes)) && isset($data[$attribute])) {
                    $instance->$attribute = $data[$attribute];
                }
            }
        } else {
            $instance = new self();
            $instance->load($data);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        if (empty($this->items)) {
            $this->items = self::find()
                ->select(['fio', 'email', 'phone'])
                ->asArray()
                ->all();
        }

        return $this->items;
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
    public function getItemsIndex(): array
    {
        if (empty($this->itemsIndex)) {
            $this->itemsIndex = self::find()
                ->select(['id'])
                ->asArray()
                ->all();
        }

        return $this->itemsIndex;
    }
}