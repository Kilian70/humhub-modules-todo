<?php

use humhub\helpers\Html;

/* @var $tasks \humhub\modules\todo\models\Task[] */
/* @var $space \humhub\modules\space\models\Space */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><i class="fa fa-check-square-o"></i> <?= Yii::t('TodoModule.base', 'ToDo – Aufgaben') ?></strong>
        <span class="pull-right">
            <?= Html::a('Zeige alle', $space->createUrl('/todo/task/index'), ['class' => 'small']) ?>
        </span>
    </div>

    <div class="list-group">
        <?php foreach ($tasks as $task): ?>
            <?php $isOverdue = $task->due_date && $task->due_date < date('Y-m-d'); ?>
            <a class="list-group-item" href="<?= Html::encode($space->createUrl('/todo/task/view', ['id' => $task->id])) ?>">
                <div>
                    <strong><?= Html::encode($task->title) ?></strong>
                    <?php if ($isOverdue): ?>
                        <span class="label label-danger"><?= Yii::t('TodoModule.base', 'ÜBERFÄLLIG') ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($task->due_date): ?>
                    <div class="small text-muted"><?= Yii::t('TodoModule.base', 'Frist') ?> <?= Yii::$app->formatter->asDate($task->due_date, 'short') ?></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
