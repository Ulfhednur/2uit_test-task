<?php

namespace app\models;

use yii\db\ActiveRecord;

class UserRefreshToken extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_refresh_tokens';
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['refresh_token', 'user_agent'], 'string', 'max' => 1000],
            [['user_ip'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['created_at'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [
                ['created_at'],
                'default',
                'value' => (new \DateTimeImmutable(
                    'now',
                    new \DateTimeZone(\Yii::$app->timeZone))
                )->format('Y-m-d H:i:s')
            ],
            [['user_id', 'refresh_token', 'user_ip', 'user_agent', 'created_at'], 'required'],
        ];
    }
}