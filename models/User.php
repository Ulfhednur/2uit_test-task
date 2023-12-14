<?php

namespace app\models;

use OpenApi\Annotations as OA;
use yii\db\ActiveRecord;
use yii;

/**
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer", example="1", description="id"),
 *     @OA\Property(property="username", type="string", example="someuser", description="Логин"),
 *     @OA\Property(property="password", type="string", example="somepassword", description="Пароль"),
 *     @OA\Property(property="valid_until", type="string", example="2024-01-27 16:57:20", description="Дата автоматической блокировки"),
 *     @OA\Property(property="blocked", type="integer", example="0", description="Заблокирован"),
 * ),
 * @OA\Schema(
 *     schema="UserResponce",
 *     @OA\Property(property="id", type="integer", example="1", description="id"),
 *     @OA\Property(property="username", type="string", example="someuser", description="Логин"),
 *     @OA\Property(property="valid_until", type="string", example="2024-01-27 16:57:20", description="Дата автоматической блокировки"),
 *     @OA\Property(property="blocked", type="integer", example="0", description="Заблокирован"),
 * ),
 *
 * @property int|null id
 * @property string|null username
 * @property string|null password_hash
 * @property string|null auth_key
 * @property string|null access_token
 * @property string|null valid_until
 * @property int|null blocked
 */
class User extends ActiveRecord
{
    const IS_BLOCKED = 1;
    const IS_NOT_BLOCKED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'valid_until'], 'default', 'value' => null],
            [['blocked'], 'default', 'value' => self::IS_NOT_BLOCKED],
            [['username', 'password_hash'], 'required'],
            [['access_token'], 'string'],
            [['username'], 'unique', 'targetAttribute' => 'username']
        ];
    }

    public static function hashPassword(string $password): string
    {
        $salt = Yii::$app->params['passwordSalt'];
        return hash('sha256', $salt.'_'.$password);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if ($insert || array_key_exists('password_hash', $this->dirtyAttributes)){
            $this->password_hash = self::hashPassword($this->password_hash);
        }
        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (array_key_exists('password_hash', $changedAttributes)) {
            UserRefreshToken::deleteAll(['user_id' => $this->id]);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function load($data = [], $formName = null)
    {
        if (isset($data['password'])) {
            if (!isset($data['password_hash'])) {
                $data['password_hash'] = $data['password'];
            }
            unset($data['password']);
        }
        return parent::load($data, $formName);
    }
}
