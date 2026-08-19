<?php

namespace humhub\modules\todo\permissions;

use humhub\libs\BasePermission;

class ViewTasks extends BasePermission
{
    protected $moduleId = 'todo';

    protected $id = 'viewTasks';

    protected $title = 'ToDo anzeigen';

    protected $description = 'Erlaubt das Anzeigen der ToDo-Liste';

    protected $defaultState = self::STATE_ALLOW;
}