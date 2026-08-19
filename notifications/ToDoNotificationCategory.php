<?php

namespace humhub\modules\todo\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

class ToDoNotificationCategory extends NotificationCategory
{
    public $id = 'todo';

    public function getTitle()
    {
        return Yii::t('TodoModule.base', 'ToDo');
    }

    public function getDescription()
    {
        return Yii::t('TodoModule.base', 'Benachrichtigungen für ToDo-Aufgaben');
    }
}