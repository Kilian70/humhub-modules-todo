<?php

use humhub\helpers\Html;

/* @var $lists \humhub\modules\todo\models\TaskList[] */
/* @var $contentContainer \humhub\modules\space\models\Space */

$this->title = 'Aufgabenlisten verwalten';
?>

<div class="panel panel-default">
    <div class="panel-heading d-flex justify-content-between align-items-center">
        <strong>Aufgabenlisten verwalten</strong>
        <?= Html::a('Zurück', $contentContainer->createUrl('/todo/task/index', ['group' => 'list']), ['class' => 'btn btn-sm btn-default']) ?>
    </div>

    <div class="panel-body">
        <p class="text-muted">
            Hier kannst du Namen, Farbe und Reihenfolge der Aufgabenlisten dieses Spaces festlegen.
            Beim Löschen bleiben die Aufgaben erhalten und werden «Unsortiert» zugeordnet.
        </p>

        <?php foreach ($lists as $index => $list): ?>
            <div class="well well-sm d-flex align-items-center gap-2" style="margin-bottom:8px;">
                <span style="width:8px;align-self:stretch;border-radius:3px;background:<?= Html::encode($list->color) ?>;"></span>

                <?= Html::beginForm($contentContainer->createUrl('/todo/task-list/update', ['id' => $list->id]), 'post', ['class' => 'd-flex align-items-center gap-2 flex-grow-1']) ?>
                    <?= Html::textInput('name', $list->name, ['class' => 'form-control', 'maxlength' => 100, 'required' => true]) ?>
                    <?= Html::input('color', 'color', $list->color, ['class' => 'form-control', 'style' => 'width:60px;padding:3px;', 'title' => 'Farbe']) ?>
                    <?= Html::submitButton('Speichern', ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::endForm() ?>

                <div class="d-flex gap-1">
                    <?php if ($index > 0): ?>
                        <?= Html::beginForm($contentContainer->createUrl('/todo/task-list/move', ['id' => $list->id, 'direction' => 'up']), 'post', ['class' => 'd-inline']) ?>
                        <?= Html::submitButton('<i class="fa fa-arrow-up"></i>', ['class' => 'btn btn-sm btn-default', 'title' => 'Nach oben']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>

                    <?php if ($index < count($lists) - 1): ?>
                        <?= Html::beginForm($contentContainer->createUrl('/todo/task-list/move', ['id' => $list->id, 'direction' => 'down']), 'post', ['class' => 'd-inline']) ?>
                        <?= Html::submitButton('<i class="fa fa-arrow-down"></i>', ['class' => 'btn btn-sm btn-default', 'title' => 'Nach unten']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>

                    <?= Html::beginForm($contentContainer->createUrl('/todo/task-list/delete', ['id' => $list->id]), 'post', ['class' => 'd-inline']) ?>
                    <?= Html::submitButton('<i class="fa fa-trash"></i>', [
                        'class' => 'btn btn-sm btn-danger',
                        'title' => 'Löschen',
                        'data-confirm' => 'Aufgabenliste wirklich löschen? Die Aufgaben bleiben erhalten und werden Unsortiert.',
                    ]) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        <?php endforeach; ?>

        <hr>

        <h5>Neue Aufgabenliste</h5>
        <?= Html::beginForm($contentContainer->createUrl('/todo/task-list/create'), 'post', ['class' => 'd-flex gap-2']) ?>
            <?= Html::textInput('name', '', ['class' => 'form-control', 'placeholder' => 'Name der neuen Aufgabenliste', 'maxlength' => 100, 'required' => true]) ?>
            <?= Html::submitButton('<i class="fa fa-plus"></i> Hinzufügen', ['class' => 'btn btn-success']) ?>
        <?= Html::endForm() ?>
    </div>
</div>
