<?php

use humhub\components\Migration;

class m260929_000000_task_archive extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'archived_at', $this->dateTime()->null()->after('closed_by'));
        $this->addColumn('todo_task', 'archived_by', $this->integer()->null()->after('archived_at'));
        $this->createIndex('idx_todo_task_archived_at', 'todo_task', 'archived_at');
        $this->addForeignKey('fk_todo_task_archived_by', 'todo_task', 'archived_by', 'user', 'id', 'SET NULL');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_todo_task_archived_by', 'todo_task');
        $this->dropIndex('idx_todo_task_archived_at', 'todo_task');
        $this->dropColumn('todo_task', 'archived_by');
        $this->dropColumn('todo_task', 'archived_at');
    }
}
