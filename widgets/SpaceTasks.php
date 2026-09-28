<?php

namespace humhub\modules\todo\widgets;

use humhub\components\Widget;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\todo\services\WidgetTaskService;
use Yii;

class SpaceTasks extends Widget
{
    public $space;

    public function run()
    {
        if (
            !$this->space ||
            !$this->space->moduleManager->isEnabled('todo') ||
            !$this->space->permissionManager->can(new ViewTasks())
        ) {
            return '';
        }

        $limit = max(1, min(20, (int) $this->space->getSettings()->get('widgetLimit', 'todo', 5)));
        $tasks = WidgetTaskService::getSpaceTasks($this->space, $limit);

        if (empty($tasks)) {
            return '';
        }

        return $this->render('spaceTasks', [
            'tasks' => $tasks,
            'space' => $this->space,
        ]);
    }
}
