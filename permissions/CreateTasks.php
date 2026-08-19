<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;

class CreateTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'createTasks';

    protected $title = 'ToDo erstellen';

    protected $description = 'Erlaubt das Erstellen neuer Aufgaben';

    protected $defaultState = self::STATE_ALLOW;
}