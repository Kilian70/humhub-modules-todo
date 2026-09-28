<?php

namespace humhub\modules\todo\services;

use humhub\modules\space\models\Space;
use humhub\modules\todo\models\Task;
use Yii;
use yii\db\Expression;

final class OverviewTaskService
{
    public const MAX_RESULTS = 500;

    public static function getOverview(array $filters): array
    {
        $filters = self::normalizeFilters($filters);
        $userId = (int) Yii::$app->user->id;
        $query = Task::find()
            ->joinWith('content')
            ->leftJoin('todo_task_user overview_tu', 'overview_tu.task_id = todo_task.id')
            ->with(['users', 'parentTask'])
            ->andWhere(['todo_task.deleted_at' => null, 'todo_task.archived_at' => null])
            ->distinct();

        if ($filters['scope'] === 'assigned') {
            $query->andWhere(['overview_tu.user_id' => $userId]);
        } elseif ($filters['scope'] === 'created') {
            $query->andWhere(['content.created_by' => $userId]);
        } elseif ($filters['scope'] === 'mine') {
            $query->andWhere(['or', ['overview_tu.user_id' => $userId], ['content.created_by' => $userId]]);
        }

        if ($filters['space_id']) {
            $query->andWhere(['content.contentcontainer_id' => $filters['space_id']]);
        }
        if ($filters['status'] === 'active') {
            $query->andWhere(['<>', 'todo_task.status', 'geschlossen']);
        } elseif ($filters['status'] !== 'all') {
            $query->andWhere(['todo_task.status' => $filters['status']]);
        }
        if ($filters['priority'] !== 'all') {
            $query->andWhere(['todo_task.priority' => $filters['priority']]);
        }

        if ($filters['keyword'] !== '') {
            $query->andWhere(['or', ['like', 'todo_task.title', $filters['keyword']], ['like', 'todo_task.description', $filters['keyword']]]);
        }

        $query->orderBy(new Expression(
            "CASE WHEN todo_task.status = 'geschlossen' THEN 1 ELSE 0 END ASC, " .
            'CASE WHEN todo_task.due_date IS NULL THEN 1 ELSE 0 END ASC, ' .
            'todo_task.due_date ASC, todo_task.id DESC'
        ))->limit(self::MAX_RESULTS);

        $baseTasks = [];
        foreach ($query->all() as $task) {
            if ($task->canView()) {
                $baseTasks[] = $task;
            }
        }
        $tasks = array_values(array_filter($baseTasks, static fn(Task $task) => self::matchesFocus($task, $filters['focus'])));

        return [
            'tasks' => $tasks,
            'spaces' => self::getVisibleSpaces($userId),
            'filters' => $filters,
            'stats' => self::getStats($baseTasks),
            'truncated' => count($baseTasks) >= self::MAX_RESULTS,
        ];
    }

    private static function getVisibleSpaces(int $userId): array
    {
        $tasks = Task::find()->joinWith('content')->andWhere(['todo_task.deleted_at' => null, 'todo_task.archived_at' => null])
            ->leftJoin('todo_task_user overview_space_tu', 'overview_space_tu.task_id = todo_task.id')
            ->andWhere(['or', ['overview_space_tu.user_id' => $userId], ['content.created_by' => $userId]])
            ->distinct()->limit(self::MAX_RESULTS)->all();
        $spaces = [];
        foreach ($tasks as $task) {
            $space = $task->content ? $task->content->container : null;
            if ($space instanceof Space && $task->canView()) {
                $spaces[$space->id] = $space;
            }
        }
        uasort($spaces, static fn(Space $a, Space $b) => strcasecmp($a->name, $b->name));
        return $spaces;
    }

    private static function getStats(array $tasks): array
    {
        $today = date('Y-m-d');
        $soon = date('Y-m-d', strtotime('+7 days'));
        $stats = ['total' => count($tasks), 'overdue' => 0, 'soon' => 0, 'blocked' => 0];
        foreach ($tasks as $task) {
            if ($task->status !== 'geschlossen' && $task->due_date && $task->due_date < $today) $stats['overdue']++;
            if ($task->status !== 'geschlossen' && $task->due_date && $task->due_date >= $today && $task->due_date <= $soon) $stats['soon']++;
            if ($task->status !== 'geschlossen' && $task->getOpenBlockingTasks()->exists()) $stats['blocked']++;
        }
        return $stats;
    }

    private static function matchesFocus(Task $task, string $focus): bool
    {
        $today = date('Y-m-d');
        if ($focus === 'overdue') {
            return $task->status !== 'geschlossen' && $task->due_date && $task->due_date < $today;
        }
        if ($focus === 'soon') {
            return $task->status !== 'geschlossen' && $task->due_date && $task->due_date >= $today
                && $task->due_date <= date('Y-m-d', strtotime('+7 days'));
        }
        if ($focus === 'blocked') {
            return $task->status !== 'geschlossen' && $task->getOpenBlockingTasks()->exists();
        }
        if ($focus === 'subtasks') {
            return !empty($task->parent_task_id);
        }
        return true;
    }

    private static function normalizeFilters(array $input): array
    {
        $choice = static function ($value, array $allowed, string $default): string {
            $value = (string) $value;
            return in_array($value, $allowed, true) ? $value : $default;
        };
        return [
            'scope' => $choice($input['scope'] ?? 'mine', ['mine', 'assigned', 'created', 'all'], 'mine'),
            'space_id' => max(0, (int) ($input['space_id'] ?? 0)),
            'status' => $choice($input['status'] ?? 'active', ['active', 'offen', 'in_bearbeitung', 'geschlossen', 'all'], 'active'),
            'priority' => $choice($input['priority'] ?? 'all', ['all', 'niedrig', 'mittel', 'hoch'], 'all'),
            'focus' => $choice($input['focus'] ?? 'all', ['all', 'overdue', 'soon', 'blocked', 'subtasks'], 'all'),
            'keyword' => mb_substr(trim((string) ($input['keyword'] ?? '')), 0, 100),
        ];
    }
}
