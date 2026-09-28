<?php

use humhub\commands\CronController;
use humhub\modules\comment\models\Comment;
use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\space\widgets\Sidebar as SpaceSidebar;
use humhub\widgets\BaseStack;
use humhub\widgets\TopMenu;
use humhub\modules\user\widgets\AccountSettingsMenu;
use yii\db\BaseActiveRecord;
use humhub\modules\todo\Events;
use humhub\modules\todo\Module;

return [
    'id' => 'todo',
    'class' => Module::class,
    'namespace' => 'humhub\\modules\\todo',
    'events' => [
        [
            'class' => TopMenu::class,
            'event' => TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit'],
        ],
        [
            'class' => AccountSettingsMenu::class,
            'event' => AccountSettingsMenu::EVENT_INIT,
            'callback' => [Events::class, 'onAccountSettingsMenuInit'],
        ],
        [
            'class' => DashboardSidebar::class,
            'event' => BaseStack::EVENT_INIT,
            'callback' => [Events::class, 'onDashboardSidebarInit'],
        ],
        [
            'class' => SpaceSidebar::class,
            'event' => BaseStack::EVENT_INIT,
            'callback' => [Events::class, 'onSpaceSidebarInit'],
        ],
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_HOURLY_RUN,
            'callback' => [Events::class, 'onCronRun'],
        ],
        [
            'class' => Comment::class,
            'event' => BaseActiveRecord::EVENT_AFTER_INSERT,
            'callback' => [Events::class, 'onCommentCreated'],
        ],
    ],
];
