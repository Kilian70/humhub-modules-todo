<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;

$this->title = 'Meine ToDos';
?>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Meine ToDos</strong></div>

    <?php if (empty($tasks)): ?>
        <div class="panel-body text-muted">Keine offenen ToDos.</div>
    <?php else: ?>
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
                    <strong><?= Html::encode($task->title) ?></strong>
                    <?php if ($isOverdue): ?>
                        <span class="label label-danger">ÜBERFÄLLIG</span>
                    <?php endif; ?>
                    <div class="small text-muted">
                        <?= Html::encode($space->name) ?>
                        <?php if ($task->due_date): ?>
                            · Frist <?= Yii::$app->formatter->asDate($task->due_date, 'short') ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
