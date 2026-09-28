<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;

class EditTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'editTasks';

    protected $title = 'ToDo bearbeiten';

    protected $description = 'Erlaubt das Bearbeiten aller Aufgaben';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
    ];
}
