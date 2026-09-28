<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;

class DeleteTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'deleteTasks';

    protected $title = 'ToDo löschen';

    protected $description = 'Erlaubt das Löschen aller Aufgaben';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
    ];
}
