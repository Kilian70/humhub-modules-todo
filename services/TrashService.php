<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\Task;

final class TrashService
{
    public const RETENTION_DAYS = 30;

    public static function purgeExpired(): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::RETENTION_DAYS . ' days'));
        $deleted = 0;
        foreach (Task::find()->andWhere(['<=', 'todo_task.deleted_at', $cutoff])->each() as $task) {
            if ($task->content->hardDelete()) {
                $deleted++;
            }
        }
        return $deleted;
    }
}
