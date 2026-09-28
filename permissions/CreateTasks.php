<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;

class CreateTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'createTasks';

    protected $title = 'ToDo erstellen';

    protected $description = 'Erlaubt das Erstellen neuer Aufgaben';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
    ];
}
