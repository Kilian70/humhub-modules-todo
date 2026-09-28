<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\Task;
use Yii;

final class TaskExportService
{
    public static function export($contentContainer, array $filters): string
    {
        $tasks = self::findTasks($contentContainer, $filters);

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, array_map([self::class, 'safe'], [
            Yii::t('TodoModule.base', 'Titel'),
            Yii::t('TodoModule.base', 'Aufgabenliste'),
            Yii::t('TodoModule.base', 'Status'),
            Yii::t('TodoModule.base', 'Priorität'),
            Yii::t('TodoModule.base', 'Fällig'),
            Yii::t('TodoModule.base', 'Zuständig'),
            Yii::t('TodoModule.base', 'Labels'),
            Yii::t('TodoModule.base', 'Erstellt von'),
            Yii::t('TodoModule.base', 'Erstellt am'),
        ]), ';');

        foreach ($tasks as $task) {
            fputcsv($stream, array_map([self::class, 'safe'], [
                $task->title,
                $task->taskList?->name ?? '',
                self::statusLabel($task->status),
                self::priorityLabel($task->priority),
                $task->due_date ?: '',
                implode(', ', array_map(static fn($user) => $user->displayName, $task->users)),
                implode(', ', array_map(static fn($label) => $label->name, $task->taskLabels)),
                $task->content?->createdBy?->displayName ?? '',
                $task->content?->created_at ?? '',
            ]), ';');
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    public static function findTasks($contentContainer, array $filters): array
    {
        $query = Task::find()
            ->contentContainer($contentContainer)
            ->with(['taskList', 'users', 'taskLabels', 'content.createdBy'])
            ->andWhere(['todo_task.parent_task_id' => null]);

        $showTrash = ($filters['trash'] ?? null) === '1';
        $showArchive = !$showTrash && ($filters['archive'] ?? null) === '1';
        $query->andWhere($showTrash
            ? ['not', ['todo_task.deleted_at' => null]]
            : ['todo_task.deleted_at' => null]);

        if ($showArchive) {
            $query->andWhere(['not', ['todo_task.archived_at' => null]]);
        } elseif (!$showTrash) {
            $query->andWhere(['todo_task.archived_at' => null]);
        }

        if (!$showArchive && !$showTrash) {
            if (!empty($filters['done'])) {
                $query->andWhere(['todo_task.status' => 'geschlossen']);
            } elseif (($filters['view'] ?? 'list') !== 'kanban') {
                $query->andWhere(['todo_task.status' => ['offen', 'in_bearbeitung']]);
            }
        }

        if (!empty($filters['my'])) {
            $query->joinWith('taskUsers')->andWhere(['todo_task_user.user_id' => Yii::$app->user->id]);
        }
        if (in_array($filters['priority'] ?? '', ['niedrig', 'mittel', 'hoch'], true)) {
            $query->andWhere(['todo_task.priority' => $filters['priority']]);
        }
        if ((int) ($filters['list_id'] ?? 0) > 0) {
            $query->andWhere(['todo_task.task_list_id' => (int) $filters['list_id']]);
        }
        if ((int) ($filters['assignee_id'] ?? 0) > 0) {
            $query->joinWith('taskUsers')->andWhere(['todo_task_user.user_id' => (int) $filters['assignee_id']]);
        }
        if ((int) ($filters['label_id'] ?? 0) > 0) {
            $query->joinWith('taskLabels')->andWhere(['todo_task_label.id' => (int) $filters['label_id']]);
        }

        $tasks = $query->distinct()->orderBy([
            'todo_task.due_date' => SORT_ASC,
            'todo_task.created_at' => SORT_DESC,
        ])->all();
        return array_values(array_filter($tasks, static fn($task) => $task->canView()));
    }

    public static function statusLabel(string $status): string
    {
        return Yii::t('TodoModule.base', match ($status) {
            'in_bearbeitung' => 'In Bearbeitung',
            'geschlossen' => 'Geschlossen',
            default => 'Offen',
        });
    }

    public static function priorityLabel(string $priority): string
    {
        return Yii::t('TodoModule.base', match ($priority) {
            'hoch' => 'Hoch',
            'mittel' => 'Mittel',
            default => 'Niedrig',
        });
    }

    public static function safe($value): string
    {
        $value = trim((string) $value);
        return preg_match('/^[=+\-@]/u', $value) ? "'" . $value : $value;
    }
}
