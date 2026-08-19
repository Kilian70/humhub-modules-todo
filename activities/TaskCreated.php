<?php

namespace humhub\modules\todo\activities;

use Yii;
use humhub\modules\activity\components\BaseActivity;

class TaskCreated extends BaseActivity
{
    public $moduleId = 'todo';

    public function getTitle()
    {
        $user = $this->originator
            ? $this->originator->displayName
            : Yii::t('TodoModule.base', 'System');

        $title = $this->source
            ? $this->source->title
            : '#?';

        return Yii::t(
            'TodoModule.base',
            '{user} hat ToDo "{title}" erstellt',
            [
                'user' => $user,
                'title' => $title,
            ]
        );
    }
}