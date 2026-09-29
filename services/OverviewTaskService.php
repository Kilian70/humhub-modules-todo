<?php

namespace humhub\modules\todo\services;

use humhub\modules\space\models\Space;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\models\TaskDependency;
use humhub\modules\todo\permissions\ViewTasks;
use Yii;
use yii\data\Pagination;
use yii\db\Expression;

final class OverviewTaskService
{
    public const PAGE_SIZE = 25;

    public static function getOverview(array $filters): array
    {
        $filters = self::normalizeFilters($filters);
        $userId = (int) Yii::$app->user->id;
        $spaces = self::getVisibleSpaces();
        $contentContainerIds = array_map(
            static fn(Space $space): int => (int) $space->contentcontainer_id,
            $spaces
        );
        $query = Task::find()
            ->readable()
            ->leftJoin('todo_task_user overview_tu', 'overview_tu.task_id = todo_task.id')
            ->andWhere(['todo_task.deleted_at' => null, 'todo_task.archived_at' => null])
            ->andWhere(['content.contentcontainer_id' => $contentContainerIds ?: [-1]])
            ->distinct();

        if ($filters['scope'] === 'assigned') {
            $query->andWhere(['overview_tu.user_id' => $userId]);
        } elseif ($filters['scope'] === 'created') {
            $query->andWhere(['content.created_by' => $userId]);
        } elseif ($filters['scope'] === 'mine') {
            $query->andWhere(['or', ['overview_tu.user_id' => $userId], ['content.created_by' => $userId]]);
        }

        if ($filters['space_id']) {
            $selectedSpace = $spaces[$filters['space_id']] ?? null;
            $query->andWhere(['content.contentcontainer_id' => $selectedSpace ? (int) $selectedSpace->contentcontainer_id : -1]);
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

        $stats = self::getStats($query);
        self::applyFocus($query, $filters['focus']);
        $totalCount = (int) (clone $query)->count();
        $pagination = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => self::PAGE_SIZE,
            'pageSizeLimit' => [1, 100],
        ]);

        $tasks = $query->with(['users', 'parentTask'])->orderBy(new Expression(
            "CASE WHEN todo_task.status = 'geschlossen' THEN 1 ELSE 0 END ASC, " .
            'CASE WHEN todo_task.due_date IS NULL THEN 1 ELSE 0 END ASC, ' .
            'todo_task.due_date ASC, todo_task.id DESC'
        ))->offset($pagination->offset)->limit($pagination->limit)->all();

        return [
            'tasks' => $tasks,
            'spaces' => $spaces,
            'filters' => $filters,
            'stats' => $stats,
            'blockedTaskIds' => self::getBlockedTaskIds($tasks),
            'pagination' => $pagination,
            'totalCount' => $totalCount,
        ];
    }

    private static function getVisibleSpaces(): array
    {
        $spaceIds = Task::find()->readable()
            ->select('content.contentcontainer_id')
            ->andWhere(['todo_task.deleted_at' => null, 'todo_task.archived_at' => null])
            ->distinct()->column();
        $spaces = [];
        $spaceModels = Space::find()->where(['contentcontainer_id' => array_map('intval', $spaceIds)])->all();
        foreach ($spaceModels as $space) {
            if ($space->getPermissionManager()->can(new ViewTasks())) {
                $spaces[(int) $space->id] = $space;
            }
        }
        uasort($spaces, static fn(Space $a, Space $b) => strcasecmp($a->name, $b->name));
        return $spaces;
    }

    private static function getBlockedTaskIds(array $tasks): array
    {
        $taskIds = array_map(static fn(Task $task): int => (int) $task->id, $tasks);
        if ($taskIds === []) {
            return [];
        }

        $ids = TaskDependency::find()
            ->alias('dependency')
            ->select('dependency.task_id')
            ->innerJoin('todo_task blocker', 'blocker.id = dependency.blocking_task_id')
            ->where(['dependency.task_id' => $taskIds])
            ->andWhere(['<>', 'blocker.status', 'geschlossen'])
            ->distinct()
            ->column();

        return array_fill_keys(array_map('intval', $ids), true);
    }

    private static function getStats($query): array
    {
        $today = date('Y-m-d');
        $active = ['<>', 'todo_task.status', 'geschlossen'];
        return [
            'total' => (int) (clone $query)->count(),
            'overdue' => (int) (clone $query)->andWhere($active)
                ->andWhere(['<', 'todo_task.due_date', $today])->count(),
            'soon' => (int) (clone $query)->andWhere($active)
                ->andWhere(['between', 'todo_task.due_date', $today, date('Y-m-d', strtotime('+7 days'))])->count(),
            'blocked' => (int) (clone $query)->andWhere($active)
                ->andWhere(self::blockedCondition())->count(),
        ];
    }

    private static function applyFocus($query, string $focus): void
    {
        $today = date('Y-m-d');
        if ($focus === 'overdue') {
            $query->andWhere(['<>', 'todo_task.status', 'geschlossen'])
                ->andWhere(['<', 'todo_task.due_date', $today]);
        } elseif ($focus === 'soon') {
            $query->andWhere(['<>', 'todo_task.status', 'geschlossen'])
                ->andWhere(['between', 'todo_task.due_date', $today, date('Y-m-d', strtotime('+7 days'))]);
        } elseif ($focus === 'blocked') {
            $query->andWhere(['<>', 'todo_task.status', 'geschlossen'])
                ->andWhere(self::blockedCondition());
        } elseif ($focus === 'subtasks') {
            $query->andWhere(['not', ['todo_task.parent_task_id' => null]]);
        }
    }

    private static function blockedCondition(): array
    {
        $subQuery = TaskDependency::find()
            ->alias('overview_dependency')
            ->select(new Expression('1'))
            ->innerJoin('todo_task overview_blocker', 'overview_blocker.id = overview_dependency.blocking_task_id')
            ->where(new Expression('overview_dependency.task_id = todo_task.id'))
            ->andWhere(['<>', 'overview_blocker.status', 'geschlossen']);
        return ['exists', $subQuery];
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
