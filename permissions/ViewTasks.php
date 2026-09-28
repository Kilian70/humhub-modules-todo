<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;

class ViewTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'viewTasks';

    protected $title = 'ToDo anzeigen';

    protected $description = 'Erlaubt das Anzeigen der ToDo-Liste';

    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
    ];
}
