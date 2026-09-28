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

        $settings = Yii::$app->getModule('todo')->settings->contentContainer($this->space);
        $limit = max(1, min(20, (int) $settings->get('widgetLimit', 5)));
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
