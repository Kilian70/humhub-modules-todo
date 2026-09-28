<?php

use humhub\components\Migration;

class m260928_200000_task_dependencies extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_task_dependency', [
            'task_id' => $this->integer()->notNull(),
            'blocking_task_id' => $this->integer()->notNull(),
            'PRIMARY KEY(task_id, blocking_task_id)',
        ]);
        $this->createIndex('idx-todo-task-dependency-blocker', 'todo_task_dependency', 'blocking_task_id');
        $this->addForeignKey('fk-todo-task-dependency-task', 'todo_task_dependency', 'task_id', 'todo_task', 'id', 'CASCADE');
        $this->addForeignKey('fk-todo-task-dependency-blocker', 'todo_task_dependency', 'blocking_task_id', 'todo_task', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('todo_task_dependency');
    }
}
