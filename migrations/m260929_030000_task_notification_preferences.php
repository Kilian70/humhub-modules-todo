<?php

use humhub\components\Migration;

class m260929_030000_task_notification_preferences extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_task_notification_preference', [
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'mode' => $this->string(32)->notNull()->defaultValue('all'),
            'PRIMARY KEY(task_id, user_id)',
        ]);
        $this->createIndex('idx_todo_notification_preference_user', 'todo_task_notification_preference', 'user_id');
        $this->addForeignKey('fk_todo_notification_preference_task', 'todo_task_notification_preference', 'task_id', 'todo_task', 'id', 'CASCADE');
        $this->addForeignKey('fk_todo_notification_preference_user', 'todo_task_notification_preference', 'user_id', 'user', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_todo_notification_preference_user', 'todo_task_notification_preference');
        $this->dropForeignKey('fk_todo_notification_preference_task', 'todo_task_notification_preference');
        $this->dropIndex('idx_todo_notification_preference_user', 'todo_task_notification_preference');
        $this->dropTable('todo_task_notification_preference');
    }
}
