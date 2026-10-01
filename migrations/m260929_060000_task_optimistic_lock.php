<?php

use humhub\components\Migration;

class m260929_060000_task_optimistic_lock extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            'todo_task',
            'lock_version',
            $this->integer()->notNull()->defaultValue(0)->after('updated_by')
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn('todo_task', 'lock_version');
    }
}
