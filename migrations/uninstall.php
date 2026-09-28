<?php

use humhub\components\Migration;
use humhub\modules\content\models\Content;

/** Completely removes all database structures and content owned by ToDo. */
class uninstall extends Migration
{
    public function safeUp()
    {
        $taskTable = $this->db->schema->getTableSchema('todo_task', true);
        if ($taskTable !== null && isset($taskTable->columns['content_id'])) {
            $contentIds = (new \yii\db\Query())
                ->select('content_id')
                ->from('todo_task')
                ->where(['not', ['content_id' => null]])
                ->column($this->db);

            foreach ($contentIds as $contentId) {
                // HumHub's hard-delete lifecycle removes activities, comments,
                // notifications and files before the task tables are dropped.
                $content = Content::findOne($contentId);
                if ($content !== null && !$content->hardDelete()) {
                    throw new \RuntimeException("Could not delete ToDo content {$contentId}.");
                }
            }
        }

        $this->dropTableIfExists('todo_checklist_item_user');
        $this->dropTableIfExists('todo_checklist_item');
        $this->dropTableIfExists('todo_task_user');
        $this->dropTableIfExists('todo_task');
        $this->dropTableIfExists('todo_task_list');
    }

    private function dropTableIfExists(string $table): void
    {
        if ($this->db->schema->getTableSchema($table, true) !== null) {
            $this->dropTable($table);
        }
    }
}
