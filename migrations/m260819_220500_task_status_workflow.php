<?php

use humhub\components\Migration;

class m260819_220500_task_status_workflow extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'closed_at', $this->dateTime()->null());
        $this->addColumn('todo_task', 'closed_by', $this->integer()->null());

        $this->createIndex('idx-todo_task-closed-by', 'todo_task', 'closed_by');
        $this->addForeignKey(
            'fk-todo_task-closed-by',
            'todo_task',
            'closed_by',
            'user',
            'id',
            'SET NULL'
        );

        // Preserve existing completed tasks under the new status name.
        $this->update('todo_task', ['status' => 'geschlossen'], ['status' => 'erledigt']);
    }

    public function safeDown()
    {
        $this->update('todo_task', ['status' => 'erledigt'], ['status' => 'geschlossen']);

        $this->dropForeignKey('fk-todo_task-closed-by', 'todo_task');
        $this->dropIndex('idx-todo_task-closed-by', 'todo_task');
        $this->dropColumn('todo_task', 'closed_by');
        $this->dropColumn('todo_task', 'closed_at');
    }
}
