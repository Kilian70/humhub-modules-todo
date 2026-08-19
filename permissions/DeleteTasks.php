<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;

class DeleteTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'deleteTasks';

    protected $title = 'ToDo löschen';

    protected $description = 'Erlaubt das Löschen von Aufgaben';

    protected $defaultState = self::STATE_DENY;
}