<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%items}}`.
 */
class m231212_172952_create_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%items}}', [
            'id' => $this->char(32)->notNull(),
            'fio' => $this->string(1000)->notNull(),
            'email' => $this->string(255)->notNull(),
            'phone' => $this->string(15)->notNull(),
        ]);

        $this->addPrimaryKey(
            '{{%items_pk}}',
            '{{%items}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%items}}');
    }
}
