<?php

use humhub\components\Migration;

class m260819_160000_optional_calendar_sync extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_task', 'sync_to_calendar', $this->boolean()->notNull()->defaultValue(false)->after('due_date'));
        $this->addColumn('todo_task', 'calendar_entry_id', $this->integer()->null()->after('sync_to_calendar'));
        $this->createIndex('idx-todo_task-calendar-entry', 'todo_task', 'calendar_entry_id');

        $this->addColumn('todo_checklist_item', 'sync_to_calendar', $this->boolean()->notNull()->defaultValue(false)->after('due_date'));
        $this->addColumn('todo_checklist_item', 'calendar_entry_id', $this->integer()->null()->after('sync_to_calendar'));
        $this->createIndex('idx-todo_checklist_item-calendar-entry', 'todo_checklist_item', 'calendar_entry_id');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-todo_checklist_item-calendar-entry', 'todo_checklist_item');
        $this->dropColumn('todo_checklist_item', 'calendar_entry_id');
        $this->dropColumn('todo_checklist_item', 'sync_to_calendar');

        $this->dropIndex('idx-todo_task-calendar-entry', 'todo_task');
        $this->dropColumn('todo_task', 'calendar_entry_id');
        $this->dropColumn('todo_task', 'sync_to_calendar');
    }
}
