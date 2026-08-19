<?php

use yii\db\Migration;

class m260819_143000_create_checklist_item extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_checklist_item', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'is_done' => $this->boolean()->notNull()->defaultValue(false),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('idx-todo_checklist_item-task-sort', 'todo_checklist_item', ['task_id', 'sort_order']);
        $this->addForeignKey(
            'fk-todo_checklist_item-task',
            'todo_checklist_item',
            'task_id',
            'todo_task',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('todo_checklist_item');
    }
}
