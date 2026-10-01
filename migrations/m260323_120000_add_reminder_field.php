<?php

use humhub\components\Migration;

class m260323_120000_add_reminder_field extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'reminder_sent_at', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('todo_task', 'reminder_sent_at');
    }
}
