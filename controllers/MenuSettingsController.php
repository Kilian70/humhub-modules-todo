<?php

namespace humhub\modules\todo\controllers;

use humhub\components\Controller;
use humhub\modules\todo\models\MenuSettingsForm;
use Yii;

class MenuSettingsController extends Controller
{
    protected function getAccessRules()
    {
        return [['login']];
    }

    public function actionIndex()
    {
        $model = MenuSettingsForm::loadCurrent();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('TodoModule.base', 'ToDo-Menüeinstellungen gespeichert.'));
            return $this->refresh();
        }

        return $this->render('index', ['model' => $model]);
    }
}
