<?php

use humhub\components\Migration;

class m260928_230000_task_labels extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_task_label', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'name' => $this->string(60)->notNull(),
            'color' => $this->string(7)->notNull()->defaultValue('#6c757d'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer(),
        ]);
        $this->createIndex('ux_todo_task_label_space_name', 'todo_task_label', ['space_id', 'name'], true);
        $this->createIndex('idx_todo_task_label_space_sort', 'todo_task_label', ['space_id', 'sort_order']);
        $this->addForeignKey('fk_todo_task_label_space', 'todo_task_label', 'space_id', 'space', 'id', 'CASCADE');

        $this->createTable('todo_task_label_map', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'label_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_todo_task_label_map', 'todo_task_label_map', ['task_id', 'label_id'], true);
        $this->addForeignKey('fk_todo_task_label_map_task', 'todo_task_label_map', 'task_id', 'todo_task', 'id', 'CASCADE');
        $this->addForeignKey('fk_todo_task_label_map_label', 'todo_task_label_map', 'label_id', 'todo_task_label', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('todo_task_label_map');
        $this->dropTable('todo_task_label');
    }
}
