<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use Yii;

class CreateTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'createTasks';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
    ];

    public function getTitle()
    {
        return Yii::t('TodoModule.base', 'ToDo erstellen');
    }

    public function getDescription()
    {
        return Yii::t('TodoModule.base', 'Erlaubt das Erstellen neuer Aufgaben');
    }
}
