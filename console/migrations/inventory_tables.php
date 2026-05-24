<?php

use yii\db\Migration;

/**
 * Class m260525_100000_create_inventory_tables
 */
class m260525_100000_create_inventory_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // Использование InnoDB критично для поддержки транзакций и row-level locking
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 1. Таблица товаров/склада
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'sku' => $this->string(64)->notNull()->unique(),
            'title' => $this->string(255)->notNull(),
            'quantity' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        // Index для быстрого поиска по артикулу (SKU)
        $this->createIndex('{{%idx-products-sku}}', '{{%products}}', 'sku');

        // 2. Таблица резервирования
        $this->createTable('{{%reservations}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'order_id' => $this->string(64)->notNull(),
            'quantity' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('pending'), // pending, confirmed, expired
            'expire_at' => $this->integer()->notNull(), // Timestamp окончания резерва (текущее время + 15 мин)
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        // Внешний ключ на таблицу товаров
        $this->addForeignKey(
            '{{%fk-reservations-product_id}}',
            '{{%reservations}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Составной индекс: критически важен для консольного воркера, 
        // который будет ежеминутно искать протухшие резервы (status = pending AND expire_at < NOW)
        $this->createIndex(
            '{{%idx-reservations-status-expire_at}}',
            '{{%reservations}}',
            ['status', 'expire_at']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-reservations-product_id}}', '{{%reservations}}');
        $this->dropTable('{{%reservations}}');
        $this->dropTable('{{%products}}');
    }
}