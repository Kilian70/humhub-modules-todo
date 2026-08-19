<?php

namespace humhub\modules\todo\notifications;

use Yii;
use yii\helpers\Html;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\todo\notifications\ToDoNotificationCategory;

class TaskAssigned extends BaseNotification
{
    public $moduleId = 'todo';


public function category()
{
    return new ToDoNotificationCategory();
}


    public function getTitle()
    {
        return Yii::t(
            'TodoModule.base',
            '{user} hat dir eine Aufgabe zugewiesen'
        );
    }


    public function html()
    {
        return Yii::t(
            'TodoModule.base',
            '{user} hat dir eine Aufgabe zugewiesen: {title}',
            [
                '{user}' => Html::encode($this->originator->displayName),
                '{title}' => Html::encode($this->source->title),
            ]
        );
    }


    public function getUrl()
    {
        return $this->source->content->container->createUrl(
            '/todo/task/view',
            ['id' => $this->source->id]
        );
    }


    public function getMailSubject()
    {
        return Yii::t(
            'TodoModule.base',
            'Neue ToDo-Aufgabe zugewiesen'
        );
    }


    public function getMailMessage()
    {
        return Yii::t(
            'TodoModule.base',
            '{user} hat dir eine Aufgabe zugewiesen: {title}',
            [
                '{user}' => $this->originator->displayName,
                '{title}' => $this->source->title,
            ]
        );
    }
    public function isValidRecipient($user)
    {
        if (!$this->source || !$this->source->content || !$this->source->content->container) {
            return false;
        }

        $container = $this->source->content->container;

        return $this->source->content->canView($user)
            && $container->getPermissionManager($user)->can(new ViewTasks());
    }


}