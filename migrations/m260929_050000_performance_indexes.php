<?php

use yii\db\Migration;

class m260929_050000_performance_indexes extends Migration
{
    public function safeUp(): void
    {
        // Main list, Kanban and reminder filters.
        $this->createIndex(
            'idx_todo_task_active_due',
            'todo_task',
            ['deleted_at', 'archived_at', 'status', 'due_date']
        );

        // Automatic archiving of completed tasks.
        $this->createIndex(
            'idx_todo_task_auto_archive',
            'todo_task',
            ['status', 'archived_at', 'deleted_at', 'closed_at']
        );

        // Reverse lookups used by the assignee and label filters.
        $this->createIndex(
            'idx_todo_task_user_user_task',
            'todo_task_user',
            ['user_id', 'task_id']
        );
        $this->createIndex(
            'idx_todo_task_label_map_label_task',
            'todo_task_label_map',
            ['label_id', 'task_id']
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx_todo_task_label_map_label_task', 'todo_task_label_map');
        $this->dropIndex('idx_todo_task_user_user_task', 'todo_task_user');
        $this->dropIndex('idx_todo_task_auto_archive', 'todo_task');
        $this->dropIndex('idx_todo_task_active_due', 'todo_task');
    }
}
