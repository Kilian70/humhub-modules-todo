<?php

namespace humhub\modules\todo\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\todo\models\TaskLabel;
use humhub\modules\todo\permissions\EditTasks;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class LabelController extends ContentContainerController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = ['class' => VerbFilter::class, 'actions' => [
            'create' => ['POST'], 'update' => ['POST'], 'delete' => ['POST'],
        ]];
        return $behaviors;
    }

    private function requirePermission(): void
    {
        if (!$this->contentContainer) throw new HttpException(404, 'Kein Space gefunden.');
        if (!$this->contentContainer->permissionManager->can(new EditTasks())) throw new ForbiddenHttpException();
    }

    private function findLabel(int $id): TaskLabel
    {
        $label = TaskLabel::findOne(['id' => $id, 'space_id' => $this->contentContainer->id]);
        if (!$label) throw new NotFoundHttpException();
        return $label;
    }

    public function actionIndex()
    {
        $this->requirePermission();
        return $this->render('index', ['labels' => TaskLabel::findForSpace((int) $this->contentContainer->id), 'contentContainer' => $this->contentContainer]);
    }

    public function actionCreate()
    {
        $this->requirePermission();
        $count = (int) TaskLabel::find()->where(['space_id' => $this->contentContainer->id])->count();
        $label = new TaskLabel([
            'space_id' => $this->contentContainer->id,
            'name' => trim((string) Yii::$app->request->post('name')),
            'color' => (string) Yii::$app->request->post('color', '#6f42c1'),
            'sort_order' => ($count + 1) * 10,
        ]);
        if (!$label->save()) Yii::$app->session->setFlash('error', implode(' ', $label->getErrorSummary(true)));
        return $this->redirect($this->contentContainer->createUrl('/todo/label/index'));
    }

    public function actionUpdate($id)
    {
        $this->requirePermission();
        $label = $this->findLabel((int) $id);
        $label->name = trim((string) Yii::$app->request->post('name'));
        $label->color = (string) Yii::$app->request->post('color', $label->color);
        if (!$label->save()) Yii::$app->session->setFlash('error', implode(' ', $label->getErrorSummary(true)));
        return $this->redirect($this->contentContainer->createUrl('/todo/label/index'));
    }

    public function actionDelete($id)
    {
        $this->requirePermission();
        $this->findLabel((int) $id)->delete();
        return $this->redirect($this->contentContainer->createUrl('/todo/label/index'));
    }
}
