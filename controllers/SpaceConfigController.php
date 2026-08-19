<?php

namespace humhub\modules\todo\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\todo\models\SpaceSettingsForm;
use Yii;
use yii\web\ForbiddenHttpException;

class SpaceConfigController extends ContentContainerController
{
    public function actionIndex()
    {
        $space = $this->contentContainer;

        if (!$space || !$space->isAdmin()) {
            throw new ForbiddenHttpException();
        }

        $model = new SpaceSettingsForm();
        $model->loadSettings($space);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->saveSettings($space);
            Yii::$app->session->setFlash('success', 'ToDo-Einstellungen für diesen Space gespeichert.');
            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
            'space' => $space,
        ]);
    }
}
