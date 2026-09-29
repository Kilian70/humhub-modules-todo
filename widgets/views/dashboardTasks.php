<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;

/* @var $tasks \humhub\modules\todo\models\Task[] */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><i class="fa fa-check-square-o"></i> <?= Yii::t('TodoModule.base', 'Meine ToDos') ?></strong>
        <span class="pull-right">
            <?= Html::a('Zeige alle', ['/todo/overview/index'], ['class' => 'small']) ?>
        </span>
    </div>

    <div class="list-group">
        <?php foreach ($tasks as $task): ?>
            <?php
            $space = $task->content ? $task->content->container : null;
            if (!$space instanceof Space) {
                continue;
            }
            $isOverdue = $task->due_date && $task->due_date < date('Y-m-d');
            ?>
            <a class="list-group-item" href="<?= Html::encode($space->createUrl('/todo/task/view', ['id' => $task->id])) ?>">
                <div>
                    <strong><?= Html::encode($task->title) ?></strong>
                    <?php if ($isOverdue): ?>
                        <span class="label label-danger"><?= Yii::t('TodoModule.base', 'ÜBERFÄLLIG') ?></span>
                    <?php endif; ?>
                </div>
                <div class="small text-muted">
                    <?= Html::encode($space->name) ?>
                    <?php if ($task->due_date): ?>
                        · Frist <?= Yii::$app->formatter->asDate($task->due_date, 'short') ?>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
