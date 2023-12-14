<?php

namespace app\models;

use OpenApi\Annotations as OA;
use Yii;
use yii\base\Model;

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
class LoginForm extends Model
{
    public string $username;
    public string $password;

    private Identity|bool|null $_user = false;

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
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Валидатор пароля
     *
     * @param string $attribute
     */
    public function validatePassword(string $attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Не правильно имя пользователя или пароль.');
            }
        }
    }

    /**
     * Авторизует пользователя по паре логин-пароль.
     * @return bool
     */
    public function login(): bool
    {
        if ($this->validate()) {
            $user = $this->getUser();
            return Yii::$app->user->login($user);
        }
        return false;
    }

    /**
     * Находит пользователя по логину
     * @return Identity|bool|null
     */
    public function getUser(): Identity|bool|null
    {
        if ($this->_user === false) {
            $this->_user = Identity::findByUsername($this->username);
        }

        return $this->_user;
    }
}
