<?php

use humhub\components\Migration;

class m260928_120000_task_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_task_history', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'event' => $this->string(50)->notNull(),
            'message' => $this->string(500)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx-todo-history-task-created', 'todo_task_history', ['task_id', 'created_at']);
        $this->addForeignKey('fk-todo-history-task', 'todo_task_history', 'task_id', 'todo_task', 'id', 'CASCADE');
        $this->addForeignKey('fk-todo-history-user', 'todo_task_history', 'user_id', 'user', 'id', 'SET NULL');
    }

    public function safeDown()
    {
        $this->dropTable('todo_task_history');
    }
}
