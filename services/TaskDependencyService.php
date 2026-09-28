<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\TaskDependency;

final class TaskDependencyService
{
    public static function wouldCreateCycle(int $taskId, int $blockingTaskId): bool
    {
        if ($taskId === $blockingTaskId) {
            return true;
        }

        $pending = [$blockingTaskId];
        $seen = [];
        while ($pending) {
            $current = array_pop($pending);
            if ($current === $taskId) {
                return true;
            }
            if (isset($seen[$current])) {
                continue;
            }
            $seen[$current] = true;
            foreach (TaskDependency::find()->select('blocking_task_id')->where(['task_id' => $current])->column() as $next) {
                $pending[] = (int) $next;
            }
        }

        return false;
    }
}
