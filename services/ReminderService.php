<?php

namespace humhub\modules\todo\services;

use Yii;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\notifications\TaskReminder;
use humhub\modules\user\models\User;
use humhub\modules\todo\permissions\ViewTasks;

class ReminderService
{
    public static function run()
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

			$tasks = Task::find()
			->with(['users', 'content.createdBy'])
			->andWhere(['!=', 'todo_task.status', 'geschlossen'])
			->andWhere(['<=', 'todo_task.due_date', $tomorrow])
			->all();


        foreach ($tasks as $task) {

            if (!$task->due_date) {
                continue;
            }


            /*
            --------------------------------------------------
            Reminder-Zeitfenster prüfen
            --------------------------------------------------
            */

            $lastReminderDate = null;

            if ($task->reminder_sent_at) {

                $lastReminderDate = date(
                    'Y-m-d',
                    strtotime($task->reminder_sent_at)
                );
            }


            $sendReminder = false;


            // morgen fällig
            if ($task->due_date === $tomorrow) {

                if ($lastReminderDate !== $today) {
                    $sendReminder = true;
                }
            }

            // heute fällig
            elseif ($task->due_date === $today) {

                if ($lastReminderDate !== $today) {
                    $sendReminder = true;
                }
            }

            // überfällig
            elseif ($task->due_date < $today) {

                if ($lastReminderDate !== $today) {
                    $sendReminder = true;
                }
            }


            if (!$sendReminder) {
                continue;
            }


            /*
            --------------------------------------------------
            Empfänger bestimmen
            --------------------------------------------------
            */

			$recipients = [];
			
			foreach ($task->users as $user) {
			
				if ($user instanceof User) {
					$recipients[$user->id] = $user;
				}
			}
			
			if ($task->created_by) {

    $creator = User::findOne($task->created_by);

    if ($creator instanceof User) {
        $recipients[$creator->id] = $creator;
    }
}

            // fallback: creator
            if (empty($recipients) && $task->content && $task->content->created_by) {

                $creator = $task->content->createdBy;

                if ($creator instanceof User) {
                    $recipients[$creator->id] = $creator;
                }
            }


            if (empty($recipients)) {
                continue;
            }


			/*
			--------------------------------------------------
			Notification senden
			--------------------------------------------------
			*/
			
            $systemUser = User::findOne(1);
            $container = $task->content ? $task->content->container : null;

            if (!$systemUser || !$container) {
                continue;
            }

            foreach ($recipients as $user) {
                if (!$task->content->canView($user)
                    || !$container->getPermissionManager($user)->can(new ViewTasks())) {
                    continue;
                }

                TaskReminder::instance()
                    ->from($systemUser)
                    ->about($task)
                    ->send($user);
            }


            /*
            --------------------------------------------------
            Reminder-Zeit speichern
            --------------------------------------------------
            */

            $task->reminder_sent_at = date('Y-m-d H:i:s');
            $task->updateAttributes(['reminder_sent_at']);
        }
    }
}