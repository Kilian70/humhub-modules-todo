<?php

namespace humhub\modules\todo\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\models\TaskTemplate;
use humhub\modules\todo\permissions\CreateTasks;
use humhub\modules\todo\permissions\EditTasks;
use humhub\modules\todo\permissions\ViewTasks;
use humhub\modules\todo\services\TaskHistoryService;
use humhub\modules\todo\services\TaskTemplateService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class TemplateController extends ContentContainerController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => ['save-from-task' => ['POST'], 'use' => ['POST'], 'delete' => ['POST']],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        $this->requirePermission(ViewTasks::class);
        return $this->render('index', [
            'templates' => TaskTemplate::find()->where(['space_id' => $this->contentContainer->id])->orderBy(['title' => SORT_ASC])->all(),
            'contentContainer' => $this->contentContainer,
            'canCreate' => $this->contentContainer->permissionManager->can(new CreateTasks()),
        ]);
    }

    public function actionSaveFromTask($id)
    {
        $this->requirePermission(CreateTasks::class);
        $task = Task::find()->contentContainer($this->contentContainer)->andWhere(['todo_task.id' => (int) $id])->one();
        if (!$task || !$task->canView()) {
            throw new NotFoundHttpException();
        }
        $template = TaskTemplateService::fromTask($task, (int) $this->contentContainer->id);
        if (!$template->save()) {
            Yii::$app->session->setFlash('error', Yii::t('TodoModule.base', 'Die Vorlage konnte nicht gespeichert werden.'));
        } else {
            TaskHistoryService::record($task, 'template_created', 'Als Vorlage gespeichert: ' . $template->title);
            Yii::$app->session->setFlash('success', Yii::t('TodoModule.base', 'Aufgabe wurde als Vorlage gespeichert.'));
        }
        return $this->redirect($this->contentContainer->createUrl('/todo/template/index'));
    }

    public function actionUpdate($id)
    {
        $template = $this->findTemplate($id);
        $this->requireTemplateManagement($template);
        if ($template->load(Yii::$app->request->post()) && $template->save()) {
            Yii::$app->session->setFlash('success', Yii::t('TodoModule.base', 'Vorlage gespeichert.'));
            return $this->redirect($this->contentContainer->createUrl('/todo/template/index'));
        }
        return $this->render('update', ['model' => $template, 'contentContainer' => $this->contentContainer]);
    }

    public function actionUse($id)
    {
        $this->requirePermission(CreateTasks::class);
        $template = $this->findTemplate($id);
        $task = TaskTemplateService::createTask($template, $this->contentContainer);
        if (!$task) {
            Yii::$app->session->setFlash('error', Yii::t('TodoModule.base', 'Die Aufgabe konnte nicht aus der Vorlage erstellt werden.'));
            return $this->redirect($this->contentContainer->createUrl('/todo/template/index'));
        }
        return $this->redirect($this->contentContainer->createUrl('/todo/task/update', ['id' => $task->id]));
    }

    public function actionDelete($id)
    {
        $template = $this->findTemplate($id);
        $this->requireTemplateManagement($template);
        $template->delete();
        Yii::$app->session->setFlash('success', Yii::t('TodoModule.base', 'Vorlage gelöscht.'));
        return $this->redirect($this->contentContainer->createUrl('/todo/template/index'));
    }

    private function findTemplate($id): TaskTemplate
    {
        $template = TaskTemplate::findOne(['id' => (int) $id, 'space_id' => (int) $this->contentContainer->id]);
        if (!$template) {
            throw new NotFoundHttpException();
        }
        return $template;
    }

    private function requirePermission(string $permission): void
    {
        if (!$this->contentContainer || !$this->contentContainer->permissionManager->can(new $permission())) {
            throw new ForbiddenHttpException();
        }
    }

    private function requireTemplateManagement(TaskTemplate $template): void
    {
        if (!$template->canManage() && !$this->contentContainer->permissionManager->can(new EditTasks())) {
            throw new ForbiddenHttpException();
        }
    }
}
