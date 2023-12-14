<?php

namespace app\models;

use yii\base\BaseObject;
use yii\web\IdentityInterface;

/**
 * Class Identity
 * @package app\modules\auth\models
 */
class Identity extends BaseObject implements IdentityInterface
{
    public int $id;
    public string $username;
    public string $password_hash;
    public string $auth_key;
    public string $access_token;

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public static function findIdentity($id): ?Identity
    {
        $now = (new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone)))->format('Y-m-d H:i:s');
        $user = User::find()
            ->where(['id' => (int) $id, 'blocked' => User::IS_NOT_BLOCKED])
            ->andWhere(['OR', ['>=', 'valid_until', $now], ['IS', 'valid_until', null]])
            ->one();

        if (!empty($user)) {
            return new static($user->toArray(['id', 'username', 'password_hash', 'auth_key', 'access_token']));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public static function findIdentityByAccessToken($token, $type = null): ?Identity
    {
        $now = (new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone)))->format('Y-m-d H:i:s');
        $user = User::find()
            ->where(['access_token' => $token, 'blocked' => User::IS_NOT_BLOCKED])
            ->andWhere(['OR', ['>=', 'valid_until', $now], ['IS', 'valid_until', null]])
            ->one();

        if (!empty($user)) {
            $userArr = $user->toArray(['id', 'username', 'password_hash', 'auth_key', 'access_token']);
            return new static($userArr);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public static function findIdentityByRefreshToken($token, $type = null): ?Identity
    {
        $now = (new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone)))->format('Y-m-d H:i:s');
        $user = User::find()
            ->where(['id' => $token->user_id, 'blocked' => User::IS_NOT_BLOCKED])
            ->andWhere(['OR', ['>=', 'valid_until', $now], ['IS', 'valid_until', null]])
            ->one();

        if (!empty($user)) {
            $userArr = $user->toArray(['id', 'username', 'password_hash', 'auth_key', 'access_token']);
            return new static($userArr);
        }

        return null;
    }

    /**
     * Поиск по логину
     *
     * @param string $username
     * @return Identity|null
     * @throws \Exception
     */
    public static function findByUsername(string $username): ?Identity
    {
        $now = (new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone)))->format('Y-m-d H:i:s');
        $user = User::find()
            ->where(['username' => $username, 'blocked' => User::IS_NOT_BLOCKED])
            ->andWhere(['OR', ['>=', 'valid_until', $now], ['IS', 'valid_until', null]])
            ->one();

        if (!empty($user)) {
            return new static($user->toArray(['id', 'username', 'password_hash', 'auth_key']));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Проверяем пароль
     *
     * @param string $password пароль для проверки
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        return $this->password_hash === User::hashPassword($password);
    }

}
