<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\Task;
use humhub\modules\todo\models\TaskNotificationPreference;
use humhub\modules\user\models\User;
use Yii;

final class TaskNotificationPreferenceService
{
    public const ALL = 'all';
    public const IMPORTANT = 'important';
    public const REMINDERS = 'reminders';
    public const MUTED = 'muted';
    public const INHERIT = 'inherit';

    public const EVENT_ASSIGNMENT = 'assignment';
    public const EVENT_COMMENT = 'comment';
    public const EVENT_REMINDER = 'reminder';
    public const EVENT_ACTIVITY = 'activity';

    public static function getMode(int $taskId, int $userId): string
    {
        $override = TaskNotificationPreference::find()
            ->select('mode')
            ->where(['task_id' => $taskId, 'user_id' => $userId])
            ->scalar();
        return $override ?: self::getDefaultMode($userId);
    }

    public static function getOverrideMode(int $taskId, int $userId): string
    {
        return (string) (TaskNotificationPreference::find()
            ->select('mode')
            ->where(['task_id' => $taskId, 'user_id' => $userId])
            ->scalar() ?: self::INHERIT);
    }

    public static function getDefaultMode(int $userId): string
    {
        $user = User::findOne($userId);
        if (!$user) {
            return self::ALL;
        }
        $mode = (string) Yii::$app->getModule('todo')->settings
            ->contentContainer($user)
            ->get('notificationDefault', self::ALL);
        return in_array($mode, self::modes(), true) ? $mode : self::ALL;
    }

    public static function save(Task $task, int $userId, string $mode): bool
    {
        if ($mode === self::INHERIT) {
            TaskNotificationPreference::deleteAll(['task_id' => $task->id, 'user_id' => $userId]);
            return true;
        }
        if (!in_array($mode, self::modes(), true)) {
            return false;
        }
        $preference = TaskNotificationPreference::findOne(['task_id' => $task->id, 'user_id' => $userId])
            ?? new TaskNotificationPreference(['task_id' => $task->id, 'user_id' => $userId]);
        $preference->mode = $mode;
        return $preference->save();
    }

    public static function allows(Task $task, int $userId, string $event): bool
    {
        $mode = self::getMode((int) $task->id, $userId);
        if ($mode === self::ALL) {
            return true;
        }
        if ($mode === self::IMPORTANT) {
            return in_array($event, [self::EVENT_ASSIGNMENT, self::EVENT_COMMENT], true);
        }
        return $mode === self::REMINDERS && $event === self::EVENT_REMINDER;
    }

    public static function modes(): array
    {
        return [self::ALL, self::IMPORTANT, self::REMINDERS, self::MUTED];
    }
}
