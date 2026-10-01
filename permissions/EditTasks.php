<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use Yii;

class EditTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'editTasks';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
    ];

    public function getTitle()
    {
        return Yii::t('TodoModule.base', 'ToDo bearbeiten');
    }

    public function getDescription()
    {
        return Yii::t('TodoModule.base', 'Erlaubt das Bearbeiten aller Aufgaben');
    }
}
