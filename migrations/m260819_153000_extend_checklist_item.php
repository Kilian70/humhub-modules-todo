<?php

use humhub\components\Migration;

class m260819_153000_extend_checklist_item extends Migration
{
    public function safeUp()
    {
        $this->addColumn('todo_checklist_item', 'due_date', $this->date()->null()->after('title'));
        $this->addColumn('todo_checklist_item', 'assigned_user_id', $this->integer()->null()->after('due_date'));
        $this->addColumn('todo_checklist_item', 'completed_by', $this->integer()->null()->after('is_done'));
        $this->addColumn('todo_checklist_item', 'completed_at', $this->dateTime()->null()->after('completed_by'));

        $this->createIndex('idx-todo_checklist_item-assigned-user', 'todo_checklist_item', 'assigned_user_id');
        $this->createIndex('idx-todo_checklist_item-completed-by', 'todo_checklist_item', 'completed_by');

        $this->addForeignKey('fk-todo_checklist_item-assigned-user', 'todo_checklist_item', 'assigned_user_id', 'user', 'id', 'SET NULL');
        $this->addForeignKey('fk-todo_checklist_item-completed-by', 'todo_checklist_item', 'completed_by', 'user', 'id', 'SET NULL');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-todo_checklist_item-completed-by', 'todo_checklist_item');
        $this->dropForeignKey('fk-todo_checklist_item-assigned-user', 'todo_checklist_item');
        $this->dropIndex('idx-todo_checklist_item-completed-by', 'todo_checklist_item');
        $this->dropIndex('idx-todo_checklist_item-assigned-user', 'todo_checklist_item');
        $this->dropColumn('todo_checklist_item', 'completed_at');
        $this->dropColumn('todo_checklist_item', 'completed_by');
        $this->dropColumn('todo_checklist_item', 'assigned_user_id');
        $this->dropColumn('todo_checklist_item', 'due_date');
    }
}
