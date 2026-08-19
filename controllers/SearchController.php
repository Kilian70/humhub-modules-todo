<?php

namespace humhub\modules\todo\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\permissions\ViewTasks;
use yii\web\ForbiddenHttpException;
use Yii;
use yii\data\Pagination;

class SearchController extends ContentContainerController
{
    public function actionIndex($keyword = null)
    {
        if (!$this->contentContainer || !$this->contentContainer->permissionManager->can(new ViewTasks())) {
            throw new ForbiddenHttpException();
        }

        $query = Task::find()->contentContainer($this->contentContainer);

        /**
         * Filter: Meine Tasks
         */
        if (Yii::$app->request->get('my')) {

            $query->joinWith('taskUsers')
                  ->andWhere([
                      'todo_task_user.user_id' => Yii::$app->user->id
                  ]);
        }

        /**
         * Filter: Status
         */
        $status = Yii::$app->request->get('status');

        if (!empty($status)) {

            $query->andWhere([
                'todo_task.status' => $status
            ]);
        }

        /**
         * Suche
         */
        if (!empty($keyword)) {

            $query->andWhere([
                'or',
                ['like', 'todo_task.title', $keyword],
                ['like', 'todo_task.description', $keyword],
            ]);
        }

        /**
         * Gruppierung
         */
        $groupBy = Yii::$app->request->get('group');

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 25,
            'pageSizeLimit' => [1, 100],
        ]);

        $tasks = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $tasksByUser = [];

        if ($groupBy === 'user') {

            foreach ($tasks as $task) {

                if (empty($task->users)) {

                    $tasksByUser['Nicht zugewiesen'][] = $task;
                    continue;
                }

                foreach ($task->users as $user) {

                    $tasksByUser[$user->displayName][] = $task;
                }
            }

            ksort($tasksByUser);
        }

        return $this->render('index', [
            'tasks' => $tasks,
            'tasksByUser' => $tasksByUser,
            'keyword' => $keyword,
            'status' => $status,
            'contentContainer' => $this->contentContainer,
            'pagination' => $pagination,
        ]);
    }
}
