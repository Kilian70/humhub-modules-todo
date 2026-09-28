<?php

namespace humhub\modules\todo\notifications;

use Yii;
use yii\helpers\Html;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\notification\components\BaseNotification;

class TaskReminder extends BaseNotification
{
    public $moduleId = 'todo';

    /** Reminders must also reach the creator when the system user is the originator. */
    public $suppressSendToOriginator = false;

    public function category()
    {
        return new ToDoNotificationCategory();
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

    public function getPriority()
    {
        return self::PRIORITY_HIGH;
    }

		public function getGroupKey()
		{
			return 'TaskReminder_' . $this->source->id;
		}

		private function getDueState()
	{
		$task = $this->source;
	
		if (!$task || !$task->due_date) {
			return null;
		}

        $due = strtotime($task->due_date);
        $today = strtotime(date('Y-m-d'));

        if ($due < $today) {
            return 'overdue';
        }

        if ($due == $today) {
            return 'today';
        }

        return 'upcoming';
    }


    public function getTitle()
    {
        switch ($this->getDueState()) {

            case 'overdue':
                return Yii::t('TodoModule.base', 'Aufgabe überfällig');

            case 'today':
                return Yii::t('TodoModule.base', 'Aufgabe heute fällig');

            default:
                return Yii::t('TodoModule.base', 'Aufgabe bald fällig');
        }
    }


    public function html()
    {
        if (!$this->source) {
            return '';
        }

        switch ($this->getDueState()) {

            case 'overdue':
                return Yii::t(
                    'TodoModule.base',
                    'Die Aufgabe "{title}" ist überfällig',
                    ['{title}' => Html::encode($this->source->title)]
                );

            case 'today':
                return Yii::t(
                    'TodoModule.base',
                    'Die Aufgabe "{title}" ist heute fällig',
                    ['{title}' => Html::encode($this->source->title)]
                );

            default:
                return Yii::t(
                    'TodoModule.base',
                    'Die Aufgabe "{title}" ist bald fällig',
                    ['{title}' => Html::encode($this->source->title)]
                );
        }
    }


    public function getUrl()
    {
        if (!$this->source || !$this->source->content || !$this->source->content->container) {
            return null;
        }

        return $this->source->content->container->createUrl(
            '/todo/task/view',
            ['id' => $this->source->id]
        );
    }


    public function getMailSubject()
    {
        switch ($this->getDueState()) {

            case 'overdue':
                return Yii::t('TodoModule.base', 'Erinnerung: Aufgabe überfällig');

            case 'today':
                return Yii::t('TodoModule.base', 'Erinnerung: Aufgabe heute fällig');

            default:
                return Yii::t('TodoModule.base', 'Erinnerung: Aufgabe bald fällig');
        }
    }


    public function getMailMessage()
    {
        if (!$this->source) {
            return '';
        }

        switch ($this->getDueState()) {

            case 'overdue':
                return Yii::t(
                    'TodoModule.base',
                    'Die Aufgabe "{title}" ist überfällig.',
                    ['{title}' => $this->source->title]
                );

            case 'today':
                return Yii::t(
                    'TodoModule.base',
                    'Die Aufgabe "{title}" ist heute fällig.',
                    ['{title}' => $this->source->title]
                );

            default:
                return Yii::t(
                    'TodoModule.base',
                    'Die Aufgabe "{title}" ist bald fällig.',
                    ['{title}' => $this->source->title]
                );
        }
    }
}
