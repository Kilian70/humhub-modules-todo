<?php

namespace humhub\modules\todo\services;

use humhub\modules\space\models\Space;
use humhub\modules\todo\models\Task;
use Yii;

final class AutoArchiveService
{
    public const ALLOWED_DAYS = [30, 60, 90];

    public static function run(): int
    {
        $archived = 0;

        foreach (Space::find()->each() as $space) {
            if (!$space->moduleManager->isEnabled('todo')) {
                continue;
            }

            $settings = Yii::$app->getModule('todo')->settings->contentContainer($space);
            $days = (int) $settings->get('autoArchiveDays', 0);
            if (!in_array($days, self::ALLOWED_DAYS, true)) {
                continue;
            }

            $cutoff = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
            $tasks = Task::find()
                ->contentContainer($space)
                ->andWhere(['todo_task.status' => 'geschlossen', 'todo_task.archived_at' => null])
                ->andWhere(['not', ['todo_task.closed_at' => null]])
                ->andWhere(['<=', 'todo_task.closed_at', $cutoff])
                ->each();

            foreach ($tasks as $task) {
                $archivedAt = date('Y-m-d H:i:s');
                $updated = Task::updateAll(
                    ['archived_at' => $archivedAt, 'archived_by' => null],
                    ['id' => (int) $task->id, 'archived_at' => null, 'status' => 'geschlossen']
                );
                if (!$updated) {
                    continue;
                }

                $task->archived_at = $archivedAt;
                $task->archived_by = null;
                TaskHistoryService::record(
                    $task,
                    'auto_archived',
                    Yii::t('TodoModule.base', 'Aufgabe nach {days} Tagen automatisch archiviert', ['days' => $days])
                );
                $archived++;
            }
        }

        return $archived;
    }
}
