<?php

use humhub\components\Migration;

class m260928_235000_personal_kanban_order extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_kanban_order', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('ux_todo_kanban_order_task_user', 'todo_kanban_order', ['task_id', 'user_id'], true);
        $this->createIndex('idx_todo_kanban_order_user_sort', 'todo_kanban_order', ['user_id', 'sort_order']);
        $this->addForeignKey('fk_todo_kanban_order_task', 'todo_kanban_order', 'task_id', 'todo_task', 'id', 'CASCADE');
        $this->addForeignKey('fk_todo_kanban_order_user', 'todo_kanban_order', 'user_id', 'user', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('todo_kanban_order');
    }
}
