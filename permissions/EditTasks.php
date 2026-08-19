<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;

class EditTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'editTasks';

    protected $title = 'ToDo bearbeiten';

    protected $description = 'Erlaubt das Bearbeiten von Aufgaben';

    protected $defaultState = self::STATE_ALLOW;
}