<?php

use humhub\components\Migration;

class m260928_180000_task_hierarchy extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'parent_task_id', $this->integer()->null()->after('task_list_id'));
        $this->createIndex('idx-todo_task-parent', 'todo_task', 'parent_task_id');
        $this->addForeignKey(
            'fk-todo_task-parent',
            'todo_task',
            'parent_task_id',
            'todo_task',
            'id',
            'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-todo_task-parent', 'todo_task');
        $this->dropIndex('idx-todo_task-parent', 'todo_task');
        $this->dropColumn('todo_task', 'parent_task_id');
    }
}
