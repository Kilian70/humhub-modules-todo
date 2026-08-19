<?php

namespace humhub\modules\todo\search;

use humhub\modules\search\interfaces\SearchProvider;
use humhub\modules\search\engine\SearchResultSet;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\permissions\ViewTasks;
use Yii;

class TaskSearchProvider implements SearchProvider
{

    public function search($query, SearchResultSet $resultSet)
    {

        $taskQuery = Task::find()->readable();

        if ($resultSet->contentContainer !== null) {
            $taskQuery->contentContainer($resultSet->contentContainer);
        }

        $taskQuery->andWhere([
            'or',
            ['like', 'todo_task.title', $query],
            ['like', 'todo_task.description', $query],
        ]);

        foreach ($taskQuery->all() as $task) {
            $container = $task->content ? $task->content->container : null;
            $user = Yii::$app->user->identity;

            if (!$container || !$user || !$container->getPermissionManager($user)->can(new ViewTasks())) {
                continue;
            }

            $resultSet->addEntry([
                'label' => $task->title,
                'description' => $task->description,
                'url' => $task->content->container->createUrl(
                    '/todo/task/view',
                    ['id' => $task->id]
                ),
                'icon' => 'check-square',
            ]);
        }
    }
}