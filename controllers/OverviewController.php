<?php

namespace humhub\modules\todo\controllers;

use humhub\components\Controller;
use humhub\modules\todo\services\OverviewTaskService;
use Yii;

class OverviewController extends Controller
{
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/user/auth/login']);
        }

        return $this->render('index', OverviewTaskService::getOverview(Yii::$app->request->get()));
    }
}
