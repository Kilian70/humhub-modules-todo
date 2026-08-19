<?php

use yii\db\Migration;

class m260819_154000_checklist_multiple_assignees extends Migration
{
    public function safeUp()
    {
        $this->createTable('todo_checklist_item_user', [
            'checklist_item_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'PRIMARY KEY(checklist_item_id, user_id)',
        ]);

        $this->createIndex(
            'idx-todo_checklist_item_user-user',
            'todo_checklist_item_user',
            'user_id'
        );

        $this->addForeignKey(
            'fk-todo_checklist_item_user-item',
            'todo_checklist_item_user',
            'checklist_item_id',
            'todo_checklist_item',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-todo_checklist_item_user-user',
            'todo_checklist_item_user',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // Bestehende Einzel-Zuständigkeiten aus 1.3.0 übernehmen.
        $this->execute(
            'INSERT INTO {{%todo_checklist_item_user}} (checklist_item_id, user_id) '
            . 'SELECT id, assigned_user_id FROM {{%todo_checklist_item}} WHERE assigned_user_id IS NOT NULL'
        );

        $this->dropForeignKey('fk-todo_checklist_item-assigned-user', 'todo_checklist_item');
        $this->dropIndex('idx-todo_checklist_item-assigned-user', 'todo_checklist_item');
        $this->dropColumn('todo_checklist_item', 'assigned_user_id');
    }

    public function safeDown()
    {
        $this->addColumn('todo_checklist_item', 'assigned_user_id', $this->integer()->null()->after('due_date'));
        $this->createIndex('idx-todo_checklist_item-assigned-user', 'todo_checklist_item', 'assigned_user_id');
        $this->addForeignKey(
            'fk-todo_checklist_item-assigned-user',
            'todo_checklist_item',
            'assigned_user_id',
            'user',
            'id',
            'SET NULL'
        );

        // Für ein Downgrade wird, falls vorhanden, die erste Zuständigkeit übernommen.
        $this->execute(
            'UPDATE {{%todo_checklist_item}} i SET assigned_user_id = ('
            . 'SELECT MIN(u.user_id) FROM {{%todo_checklist_item_user}} u WHERE u.checklist_item_id = i.id)'
        );

        $this->dropForeignKey('fk-todo_checklist_item_user-user', 'todo_checklist_item_user');
        $this->dropForeignKey('fk-todo_checklist_item_user-item', 'todo_checklist_item_user');
        $this->dropIndex('idx-todo_checklist_item_user-user', 'todo_checklist_item_user');
        $this->dropTable('todo_checklist_item_user');
    }
}
