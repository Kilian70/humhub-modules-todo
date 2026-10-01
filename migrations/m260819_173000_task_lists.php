<?php

use humhub\components\Migration;

class m260819_173000_task_lists extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_task_list', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'color' => $this->string(7)->notNull()->defaultValue('#6c757d'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer(),
        ]);

        $this->createIndex('ux-todo_task_list-space-name', 'todo_task_list', ['space_id', 'name'], true);
        $this->createIndex('idx-todo_task_list-space-sort', 'todo_task_list', ['space_id', 'sort_order']);
        $this->addForeignKey('fk-todo_task_list-space', 'todo_task_list', 'space_id', 'space', 'id', 'CASCADE');

        $this->addColumn('todo_task', 'task_list_id', $this->integer()->null()->after('id'));
        $this->createIndex('idx-todo_task-task-list', 'todo_task', 'task_list_id');
        $this->addForeignKey('fk-todo_task-task-list', 'todo_task', 'task_list_id', 'todo_task_list', 'id', 'SET NULL');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-todo_task-task-list', 'todo_task');
        $this->dropIndex('idx-todo_task-task-list', 'todo_task');
        $this->dropColumn('todo_task', 'task_list_id');

        $this->dropForeignKey('fk-todo_task_list-space', 'todo_task_list');
        $this->dropTable('todo_task_list');
    }
}
