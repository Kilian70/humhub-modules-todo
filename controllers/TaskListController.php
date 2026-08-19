<?php

namespace humhub\modules\todo\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\todo\models\TaskList;
use humhub\modules\todo\permissions\EditTasks;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class TaskListController extends ContentContainerController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['POST'],
                'move' => ['POST'],
                'delete' => ['POST'],
            ],
        ];
        return $behaviors;
    }

    private function requireManagePermission(): void
    {
        if (!$this->contentContainer) {
            throw new HttpException(404, 'Kein Space gefunden.');
        }
        if (!$this->contentContainer->permissionManager->can(new EditTasks())) {
            throw new ForbiddenHttpException();
        }
    }

    private function findList(int $id): TaskList
    {
        $list = TaskList::findOne(['id' => $id, 'space_id' => $this->contentContainer->id]);
        if (!$list) {
            throw new NotFoundHttpException();
        }
        return $list;
    }

    public function actionIndex()
    {
        $this->requireManagePermission();

        return $this->render('index', [
            'lists' => TaskList::findForSpace((int) $this->contentContainer->id),
            'contentContainer' => $this->contentContainer,
        ]);
    }

    public function actionCreate()
    {
        $this->requireManagePermission();

        $name = trim((string) Yii::$app->request->post('name'));
        if ($name === '') {
            Yii::$app->session->setFlash('error', 'Bitte einen Namen eingeben.');
        } elseif (!TaskList::findOrCreateForSpace((int) $this->contentContainer->id, $name)) {
            Yii::$app->session->setFlash('error', 'Aufgabenliste konnte nicht erstellt werden.');
        }

        return $this->redirect($this->contentContainer->createUrl('/todo/task-list/index'));
    }

    public function actionUpdate($id)
    {
        $this->requireManagePermission();
        $list = $this->findList((int) $id);

        $list->name = trim((string) Yii::$app->request->post('name'));
        $list->color = (string) Yii::$app->request->post('color', $list->color);

        if (!$list->save()) {
            Yii::$app->session->setFlash('error', implode(' ', $list->getErrorSummary(true)));
        }

        return $this->redirect($this->contentContainer->createUrl('/todo/task-list/index'));
    }

    public function actionMove($id, $direction)
    {
        $this->requireManagePermission();
        $list = $this->findList((int) $id);

        if (!in_array($direction, ['up', 'down'], true)) {
            throw new HttpException(400);
        }

        $lists = TaskList::findForSpace((int) $this->contentContainer->id);
        $index = null;
        foreach ($lists as $i => $candidate) {
            if ((int) $candidate->id === (int) $list->id) {
                $index = $i;
                break;
            }
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($index !== null && $target >= 0 && $target < count($lists)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($lists as $i => $candidate) {
                    $candidate->updateAttributes(['sort_order' => ($i + 1) * 10]);
                }
                $list->updateAttributes(['sort_order' => ($target + 1) * 10]);
                $lists[$target]->updateAttributes(['sort_order' => ($index + 1) * 10]);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->redirect($this->contentContainer->createUrl('/todo/task-list/index'));
    }

    public function actionDelete($id)
    {
        $this->requireManagePermission();
        $list = $this->findList((int) $id);

        // FK SET NULL keeps tasks; they become "Unsortiert".
        $list->delete();

        return $this->redirect($this->contentContainer->createUrl('/todo/task-list/index'));
    }
}
