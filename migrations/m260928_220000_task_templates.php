<?php

use humhub\components\Migration;

class m260928_220000_task_templates extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_task_template', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'priority' => $this->string(50)->notNull()->defaultValue('mittel'),
            'assignee_guids' => $this->text()->null(),
            'checklist_text' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-todo-template-space-title', 'todo_task_template', ['space_id', 'title']);
        $this->addForeignKey('fk-todo-template-space', 'todo_task_template', 'space_id', 'space', 'id', 'CASCADE');
        $this->addForeignKey('fk-todo-template-created-by', 'todo_task_template', 'created_by', 'user', 'id', 'SET NULL');
        $this->addForeignKey('fk-todo-template-updated-by', 'todo_task_template', 'updated_by', 'user', 'id', 'SET NULL');
    }

    public function safeDown()
    {
        $this->dropTable('todo_task_template');
    }
}
