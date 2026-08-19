<?php

namespace humhub\modules\todo\widgets;

use humhub\components\Widget;
use humhub\modules\todo\services\WidgetTaskService;
use Yii;

class DashboardTasks extends Widget
{
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        $limit = max(1, min(20, (int) Yii::$app->getModule('todo')->settings->get('dashboardWidgetLimit', 5)));
        $tasks = WidgetTaskService::getMyTasks($limit);

        if (empty($tasks)) {
            return '';
        }

        return $this->render('dashboardTasks', [
            'tasks' => $tasks,
        ]);
    }
}
