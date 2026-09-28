<?php

use humhub\components\Migration;

class m260929_010000_task_trash extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'deleted_at', $this->dateTime()->null()->after('archived_by'));
        $this->addColumn('todo_task', 'deleted_by', $this->integer()->null()->after('deleted_at'));
        $this->createIndex('idx_todo_task_deleted_at', 'todo_task', 'deleted_at');
        $this->addForeignKey('fk_todo_task_deleted_by', 'todo_task', 'deleted_by', 'user', 'id', 'SET NULL');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_todo_task_deleted_by', 'todo_task');
        $this->dropIndex('idx_todo_task_deleted_at', 'todo_task');
        $this->dropColumn('todo_task', 'deleted_by');
        $this->dropColumn('todo_task', 'deleted_at');
    }
}
