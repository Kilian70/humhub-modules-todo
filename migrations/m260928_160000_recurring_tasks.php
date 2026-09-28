<?php

use humhub\components\Migration;

class m260928_160000_recurring_tasks extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'recurrence_type', $this->string(20)->null());
        $this->addColumn('todo_task', 'recurrence_interval', $this->integer()->notNull()->defaultValue(1));
        $this->addColumn('todo_task', 'recurrence_end_date', $this->date()->null());
        $this->addColumn('todo_task', 'recurrence_generated_at', $this->dateTime()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('todo_task', 'recurrence_generated_at');
        $this->dropColumn('todo_task', 'recurrence_end_date');
        $this->dropColumn('todo_task', 'recurrence_interval');
        $this->dropColumn('todo_task', 'recurrence_type');
    }
}
