<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\Task;
use humhub\modules\todo\notifications\TaskReminder;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\user\models\User;
use Yii;

class ReminderService
{
    private const BATCH_SIZE = 100;

    public static function run(): void
    {
        $settings = Yii::$app->getModule('todo')->settings;
        if (!(bool) $settings->get('remindersEnabled', true)) {
            return;
        }

        $today = date('Y-m-d');
        $daysBefore = max(0, min(30, (int) $settings->get('reminderDaysBefore', 3)));
        $latestDueDate = date('Y-m-d', strtotime($today . ' +' . $daysBefore . ' days'));

        $tasks = Task::find()
            ->with(['users', 'content.createdBy'])
            ->andWhere(['todo_task.deleted_at' => null, 'todo_task.archived_at' => null])
            ->andWhere(['!=', 'todo_task.status', 'geschlossen'])
            ->andWhere(['not', ['todo_task.due_date' => null]])
            ->andWhere(['<=', 'todo_task.due_date', $latestDueDate])
            ->each(self::BATCH_SIZE);

        foreach ($tasks as $task) {
            $type = ReminderPolicy::determine(
                (string) $task->due_date,
                $today,
                $daysBefore,
                (bool) $settings->get('upcomingReminderEnabled', true),
                (bool) $settings->get('dueReminderEnabled', true),
                (bool) $settings->get('overdueReminderEnabled', true),
                !empty($task->upcoming_reminder_sent_at),
                !empty($task->due_reminder_sent_at),
                !empty($task->overdue_reminder_sent_at)
            );

            if ($type === null) {
                continue;
            }

            $attribute = match ($type) {
                ReminderPolicy::UPCOMING => 'upcoming_reminder_sent_at',
                ReminderPolicy::DUE => 'due_reminder_sent_at',
                default => 'overdue_reminder_sent_at',
            };

            // Atomically claim this reminder stage. Concurrent cron processes
            // must not enqueue the same reminder more than once.
            $claimedAt = date('Y-m-d H:i:s');
            if (Task::updateAll(
                [$attribute => $claimedAt],
                ['and', ['id' => $task->id], [$attribute => null]]
            ) !== 1) {
                continue;
            }
            $task->$attribute = $claimedAt;

            $recipients = [];
            foreach ($task->users as $user) {
                if ($user instanceof User) {
                    $recipients[$user->id] = $user;
                }
            }
            if ($task->content?->createdBy instanceof User) {
                $recipients[$task->content->createdBy->id] = $task->content->createdBy;
            }

            $systemUser = User::findOne(1);
            $container = $task->content?->container;
            if (!$systemUser || !$container || $recipients === []) {
                self::releaseClaim($task, $attribute, $claimedAt);
                continue;
            }

            $sent = false;
            try {
                foreach ($recipients as $user) {
                    if (!$task->content->canView($user)
                        || !$container->getPermissionManager($user)->can(new ViewTasks())) {
                        continue;
                    }
                    if (!TaskNotificationPreferenceService::allows($task, (int) $user->id, TaskNotificationPreferenceService::EVENT_REMINDER)) {
                        continue;
                    }

                    TaskReminder::instance()->from($systemUser)->about($task)->send($user);
                    $sent = true;
                }
            } catch (\Throwable $e) {
                self::releaseClaim($task, $attribute, $claimedAt);
                Yii::error($e, __METHOD__);
                continue;
            }

            if (!$sent) {
                self::releaseClaim($task, $attribute, $claimedAt);
            }
        }
    }

    private static function releaseClaim(Task $task, string $attribute, string $claimedAt): void
    {
        Task::updateAll(
            [$attribute => null],
            ['id' => $task->id, $attribute => $claimedAt]
        );
        $task->$attribute = null;
    }
}
