<?php

use yii\db\Migration;

class m260322_180000_create_task extends Migration
{
    public function safeUp()
    {
        /*
         * Haupttabelle: todo_task
         * Wichtig:
         * content_id darf NICHT NOT NULL sein
         * (ContentActiveRecord setzt es erst nach dem Content-Save)
         */

        $this->createTable('todo_task', [

            'id' => $this->primaryKey(),

            // Pflicht für ContentActiveRecord
            'content_id' => $this->integer(),

            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'priority' => $this->string(50),
            'status' => $this->string(50),
            'due_date' => $this->date(),

            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer(),
        ]);


        /*
         * Index für ContentRelation (wichtig!)
         */

        $this->createIndex(
            'idx-todo_task-content_id',
            'todo_task',
            'content_id'
        );


        /*
         * Foreign Key → content Tabelle
         */

        $this->addForeignKey(
            'fk-todo_task-content_id',
            'todo_task',
            'content_id',
            'content',
            'id',
            'CASCADE'
        );


        /*
         * Relationstabelle: Zuständige User
         */

        $this->createTable('todo_task_user', [

            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),

        ]);


        /*
         * Composite Primary Key
         */

        $this->addPrimaryKey(
            'pk-todo_task_user',
            'todo_task_user',
            ['task_id', 'user_id']
        );


        /*
         * FK → todo_task
         */

        $this->addForeignKey(
            'fk-todo_task_user-task',
            'todo_task_user',
            'task_id',
            'todo_task',
            'id',
            'CASCADE'
        );


        /*
         * FK → user
         */

        $this->addForeignKey(
            'fk-todo_task_user-user',
            'todo_task_user',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }


    public function safeDown()
    {
        $this->dropTable('todo_task_user');
        $this->dropTable('todo_task');
    }
}