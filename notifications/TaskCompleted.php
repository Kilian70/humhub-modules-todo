<?php

namespace humhub\modules\todo\notifications;

use Yii;
use yii\helpers\Html;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\todo\notifications\ToDoNotificationCategory;

class TaskCompleted extends BaseNotification
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
            '{user} hat eine Aufgabe erledigt'
        );
    }


    public function getUrl()
    {
        if (!$this->source || !$this->source->content) {
            return '';
        }

        return $this->source->content->container->createUrl(
            '/todo/task/view',
            ['id' => $this->source->id]
        );
    }


    public function html()
    {
        if (!$this->source) {
            return '';
        }

        return Yii::t(
            'TodoModule.base',
            '{user} hat eine Aufgabe erledigt: {title}',
            [
                '{user}' => Html::encode($this->originator->displayName),
                '{title}' => Html::encode($this->source->title),
            ]
        );
    }


    public function getMailSubject()
    {
        return Yii::t(
            'TodoModule.base',
            'Aufgabe erledigt'
        );
    }


    public function getMailMessage()
    {
        if (!$this->source) {
            return '';
        }

        return Yii::t(
            'TodoModule.base',
            '{user} hat eine Aufgabe erledigt: {title}',
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