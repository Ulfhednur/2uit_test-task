<?php

namespace app\models;

use OpenApi\Annotations as OA;

use yii\base\Model;
use app\models\storage\XlsxStorage;
use app\models\storage\JsonStorage;
use app\models\storage\CacheStorage;
use app\models\storage\DBStorage;

/**
 *
 * @OA\Schema(
 *     schema="loginForm",
 *     @OA\Property(property="username", type="string", example="someuser", description="Логин"),
 *     @OA\Property(property="password", type="string", example="somepassword", description="Пароль"),
 * ),
 *
 * Модель для первичной авторизации по логину-паролю для получения токена.
 *
 * @property-read Identity|bool|null $user
 *
 */
class ItemForm extends Model
{
    const DB_STORAGE = DBStorage::class;
    const CACHE_STORAGE = CacheStorage::class;
    const JSON_STORAGE = JsonStorage::class;
    const XLSX_STORAGE = XlsxStorage::class;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';


    protected array $storages = [];

    public string $id;
    public string $fio;
    public string $email;
    public string $phone;
    public string $storage;

    public function __construct($config = [])
    {
        foreach (self::getStorageNames() as $storage) {
            $this->storages[$storage] = new $storage();
        }
        parent::__construct($config);
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
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['id', 'fio', 'email', 'phone', 'storage'];
        $scenarios[self::SCENARIO_UPDATE] = ['id', 'fio', 'email', 'phone', 'storage'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'string', 'max' => 32, 'min' => 32],
            [['id'], 'crossStorageUnique', 'on' => self::SCENARIO_CREATE],
            [['fio'], 'string', 'max' => 1000],
            [['email'], 'email', 'enableIDN' => false],
            [['phone'], 'filter', 'filter' => function($value){
                return '+' . preg_replace("/[^0-9]/", '', $value);
            },],
            [['phone'], 'validatePhoneNumber'],
            [['storage'], 'string'],
            [['storage'], 'in', 'range' => array_keys(self::getStorageNames())],
            [['id', 'fio', 'email', 'phone', 'storage'], 'required'],
        ];
    }

    /**
     * Сохраняет данные в указанное хранилище
     * @param bool $runValidation
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save(bool $runValidation = true): bool
    {
        if ($runValidation) {
            if (!$this->validate()) {
                return false;
            }
        }

        $storageClass = self::getStorageNames()[$this->storage];

        /** @var DBStorage|XlsxStorage|CacheStorage|JsonStorage $storage */

        if ($storage = $storageClass::loadData($this->toArray(['id', 'fio', 'email', 'phone']))) {
            if ($storage->save($runValidation)) {
                return true;

            }
        }

        foreach ($storage->getErrorSummary(true) as $field => $error) {
            $this->addError($field, $error);
        }
        return false;
    }

    /**
     * Загружает данные, генерирует идентификатор и, при обновлении, ищет хранилище
     *
     * {@inheritdoc}
     */
    public function load($data, $formName = null): bool
    {
        foreach (['id', 'fio', 'email', 'phone', 'storage'] as $attribute) {
            if (property_exists($this, $attribute)) {
                $this->$attribute = $data[$attribute] ?? '';
            }
        }

        $this->id = self::generateId($data['fio']);

        if($this->scenario == self::SCENARIO_UPDATE) {
            $this->storage = $this->findStorageById($this->id);
            if (!$this->storage) {
                return false;
            }
        }
        return true;
    }

    /**
     * Валидация номера телефона на соответствие международному стандарту
     * @param $attribute
     * @return bool
     */
    public function validatePhoneNumber($attribute): bool
    {
        if (!preg_match('/^\\+(\d{7,14})$/', $this->$attribute)) {
            $this->addError('phone', 'Неправильный номер телефона ' . $this->$attribute);
            return false;
        }
        return false;
    }

    /**
     * Валидация уникальности записи по всем хранилищам. Из ТЗ не ясно, должна ли запись быть уникальной в рамках
     * хранилища, либо требуется соблюдать сквозную уникальность. Раз у нас тестовое задание, я выбрал более сложный
     * в реализации вариант.
     *
     * @param $attribute
     * @return bool
     */
    public function crossStorageUnique($attribute): bool
    {
        $has = $this->findStorageById($this->$attribute);
        if ($has) {
            $this->addError('fio', 'Указанные ФИО уже содержатся в хранилище ' . $has);
            return false;
        }
        return true;
    }

    /**
     * Возвращает тип хранилища, в котором есть запись с указанным id
     * @param string $id
     * @return string|false
     */
    protected function findStorageById(string $id): string|bool
    {
        foreach ($this->storages as $storage) {
            /** @var DBStorage|XlsxStorage|CacheStorage|JsonStorage $storage */
            if ($storage->hasItem($id)) {
                return array_search($storage->getStorageName(), self::getStorageNames());
            }
        }
        return false;
    }

    /**
     * Возвращает массив имён хранилищ.
     *
     * @return string[]
     */
    public static function getStorageNames(): array
    {
        return [
            'database' => self::DB_STORAGE,
            'cache' => self::CACHE_STORAGE,
            'json' => self::JSON_STORAGE,
            'xlsx' => self::XLSX_STORAGE,
        ];
    }

    /**
     * Генерирует идентификатор
     *
     * @param string $string
     * @return string
     */
    public static function generateId(string $string): string
    {
        return md5($string);
    }
}
