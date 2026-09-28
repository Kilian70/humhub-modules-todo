<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\Task;
use Yii;

class WidgetTaskService
{
    public static function getSpaceTasks($space, int $limit): array
    {
        return Task::find()
            ->contentContainer($space)
            ->andWhere(['!=', 'todo_task.status', 'geschlossen'])
            ->orderBy(new \yii\db\Expression('CASE WHEN todo_task.due_date IS NULL THEN 1 ELSE 0 END ASC, todo_task.due_date ASC, todo_task.id DESC'))
            ->limit($limit)
            ->all();
    }

    public static function getMyTasks(int $limit = 5, ?int $hardLimit = null): array
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        $query = Task::find()
            ->joinWith('taskUsers')
            ->andWhere(['todo_task_user.user_id' => Yii::$app->user->id])
            ->andWhere(['!=', 'todo_task.status', 'geschlossen'])
            ->orderBy(new \yii\db\Expression('CASE WHEN todo_task.due_date IS NULL THEN 1 ELSE 0 END ASC, todo_task.due_date ASC, todo_task.id DESC'))
            ->distinct();

        if ($hardLimit !== null) {
            $query->limit($hardLimit);
        } else {
            // Fetch a few more than needed because canView() can filter records.
            $query->limit(max($limit * 4, 20));
        }

        $result = [];
        foreach ($query->all() as $task) {
            if (!$task->canView()) {
                continue;
            }
            $result[] = $task;
            if ($hardLimit === null && count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }

}
