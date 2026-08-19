<?php

namespace humhub\modules\todo\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\todo\permissions\ViewTasks;
use Yii;
use yii\helpers\Html;

class TaskCommented extends BaseNotification
{
    public $moduleId = 'todo';

    public function category()
    {
        return new ToDoNotificationCategory();
    }

    public function getTitle()
    {
        return Yii::t('TodoModule.base', '{user} hat eine Nachricht zu einer Aufgabe geschrieben');
    }

    public function html()
    {
        return Yii::t(
            'TodoModule.base',
            '{user} hat eine Nachricht zu «{title}» geschrieben.',
            [
                '{user}' => Html::encode($this->originator->displayName),
                '{title}' => Html::encode($this->source->title),
            ]
        );
    }

    public function getUrl()
    {
        return $this->source->content->container->createUrl('/todo/task/view', [
            'id' => $this->source->id,
        ]) . '#todo-communication';
    }

    public function getMailSubject()
    {
        return Yii::t('TodoModule.base', 'Neue Nachricht zu einer ToDo-Aufgabe');
    }

    public function getMailMessage()
    {
        return Yii::t(
            'TodoModule.base',
            '{user} hat eine Nachricht zu «{title}» geschrieben.',
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
