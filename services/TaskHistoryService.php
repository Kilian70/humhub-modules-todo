<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\Task;
use humhub\modules\todo\models\TaskHistory;
use Yii;

final class TaskHistoryService
{
    public static function record(Task $task, string $event, string $message): void
    {
        if (!$task->id || trim($message) === '') {
            return;
        }

        $history = new TaskHistory([
            'task_id' => (int) $task->id,
            'user_id' => Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id,
            'event' => $event,
            'message' => mb_substr(trim($message), 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$history->save()) {
            Yii::warning('Could not write ToDo history: ' . implode('; ', $history->getErrorSummary(true)), __METHOD__);
        }
    }
}
