<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_refresh_tokens}}`.
 */
class m231112_000002_create_user_refresh_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_refresh_tokens}}', [
            'id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'refresh_token' => $this->string(1000)->notNull(),
            'user_ip' => $this->string(50)->notNull(),
            'user_agent' => $this->string(1000)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->addPrimaryKey(
            '{{%user_refresh_tokens_pk}}',
            '{{%user_refresh_tokens}}',
            'id'
        );

        $this->alterColumn('{{%user_refresh_tokens}}', 'id', 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');

        $this->createIndex(
            '{{%user_refresh_tokens_user_id}}',
            '{{%user_refresh_tokens}}',
            'user_id'
        );

        /**
         * Внешние ключи устанавливаем только для не prod-окружения
         * Они сильно замедляют UPDATE/INSERT/DELETE и создают излишнюю нагрузку на базу
         * Консистентность данных контролируем на уровне приложения.
         */
        if (Yii::$app->params['environment'] !== 'prod') {
            $this->addForeignKey(
                '{{%user_refresh_tokens_user_id_fk}}',
                '{{%user_refresh_tokens}}',
                'user_id',
                '{{%users}}',
                'id',
                'RESTRICT'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_refresh_tokens}}');
    }
}
