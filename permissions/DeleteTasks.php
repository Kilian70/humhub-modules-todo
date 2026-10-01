<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use Yii;

class DeleteTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'deleteTasks';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
    ];

    public function getTitle()
    {
        return Yii::t('TodoModule.base', 'ToDo löschen');
    }

    public function getDescription()
    {
        return Yii::t('TodoModule.base', 'Erlaubt das Löschen aller Aufgaben');
    }
}
