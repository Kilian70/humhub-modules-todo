<?php

use humhub\components\Migration;

class m260928_140000_reminder_stages extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'upcoming_reminder_sent_at', $this->dateTime()->null());
        $this->addColumn('todo_task', 'due_reminder_sent_at', $this->dateTime()->null());
        $this->addColumn('todo_task', 'overdue_reminder_sent_at', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('todo_task', 'overdue_reminder_sent_at');
        $this->dropColumn('todo_task', 'due_reminder_sent_at');
        $this->dropColumn('todo_task', 'upcoming_reminder_sent_at');
    }
}
