<?php

namespace humhub\modules\todo;

use humhub\modules\comment\models\Comment;
use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\space\widgets\Sidebar as SpaceSidebar;
use humhub\modules\todo\widgets\DashboardTasks;
use humhub\modules\todo\widgets\SpaceTasks;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\notifications\TaskCommented;
use humhub\modules\todo\services\ReminderService;
use Yii;
use yii\base\Event;

class Events
{

    public static function onDashboardSidebarInit(Event $event): void
    {
        $settings = Yii::$app->getModule('todo')->settings;
        if (!(bool) $settings->get('dashboardWidgetEnabled', true)) {
            return;
        }

        $event->sender->addWidget(
            DashboardTasks::class,
            [],
            ['sortOrder' => (int) $settings->get('dashboardWidgetSortOrder', 300)]
        );
    }

    public static function onSpaceSidebarInit(Event $event): void
    {
        $space = $event->sender->space ?? null;
        if (!$space || !$space->isModuleEnabled('todo')) {
            return;
        }

        $settings = $space->getSettings();
        if (!(bool) $settings->get('widgetEnabled', 'todo', true)) {
            return;
        }

        $event->sender->addWidget(
            SpaceTasks::class,
            ['space' => $space],
            ['sortOrder' => (int) $settings->get('widgetSortOrder', 'todo', 300)]
        );
    }

    public static function onCronRun(): void
    {
        ReminderService::run();
    }

    /**
     * Inform assigned users when somebody writes a new top-level message or reply
     * on a ToDo task. HumHub itself continues to handle followers, mentions and
     * the normal comment notifications.
     */
    public static function onCommentCreated(Event $event): void
    {
        $comment = $event->sender;
        if (!$comment instanceof Comment || !$comment->content) {
            return;
        }

        $task = $comment->content->getPolymorphicRelation();
        if (!$task instanceof Task) {
            return;
        }

        $originator = $comment->user;
        if (!$originator) {
            return;
        }

        foreach ($task->users as $user) {
            if ((int) $user->id === (int) $originator->id) {
                continue;
            }

            Yii::$app->notification->send(
                new TaskCommented([
                    'originator' => $originator,
                    'source' => $task,
                ]),
                $user
            );
        }
    }
}
