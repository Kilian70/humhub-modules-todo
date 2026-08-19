<?php

use yii\helpers\Html;
use humhub\modules\user\widgets\Image as UserImage;

/** @var $model \humhub\modules\todo\models\Task */

$overdue = false;

if ($model->due_date && $model->status !== 'geschlossen') {
    $overdue = strtotime($model->due_date) < strtotime(date('Y-m-d'));
}

?>

<div class="todo-wall-entry">

    <strong>
        <?= Html::a(
            'ToDo: ' . Html::encode($model->title),
            $model->getUrl()
        ) ?>
    </strong>


    <?php if ($model->description): ?>

        <div class="text-muted mb-1">
            <?= Html::encode($model->description) ?>
        </div>

    <?php endif; ?>


    <div class="small text-muted">

        <?php if ($model->due_date): ?>

            <span>
                📅 <?= Yii::$app->formatter->asDate($model->due_date) ?>
            </span>

        <?php endif; ?>


        <?php if ($model->priority): ?>

            <span class="ms-2">
                ⚑ <?= Html::encode($model->priority) ?>
            </span>

        <?php endif; ?>

    </div>


    <?php if ($overdue): ?>

        <div class="text-danger small mt-1">
            ⚠ <?= Yii::t('TodoModule.base', 'überfällig') ?>
        </div>

    <?php endif; ?>


    <?php if ($model->users): ?>

        <div class="mt-2 small text-muted">

            <strong>
                <?= Yii::t('TodoModule.base', 'Zuständig:') ?>
            </strong>

            <div class="mt-1">

                <?php foreach ($model->users as $user): ?>

                    <?= UserImage::widget([
                        'user' => $user,
                        'width' => 24
                    ]) ?>

                <?php endforeach; ?>

            </div>

        </div>

    <?php elseif ($model->content && $model->content->createdBy): ?>

        <div class="mt-2 small text-muted">

            <strong>
                <?= Yii::t('TodoModule.base', 'Erstellt von:') ?>
            </strong>

            <?= UserImage::widget([
                'user' => $model->content->createdBy,
                'width' => 24
            ]) ?>

        </div>

    <?php endif; ?>

</div>