<?php

namespace humhub\modules\todo\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\todo\models\SettingsForm;
use Yii;

class ConfigController extends Controller
{
    public function actionIndex()
    {
        $model = new SettingsForm();
        $model->loadSettings();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->saveSettings();
            Yii::$app->session->setFlash('success', 'ToDo-Einstellungen gespeichert.');
            return $this->refresh();
        }

        return $this->render('index', ['model' => $model]);
    }
}
