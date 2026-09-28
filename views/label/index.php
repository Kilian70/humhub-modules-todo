<?php

use yii\helpers\Html;

$this->title = Yii::t('TodoModule.base', 'Labels verwalten');
?>
<div class="panel panel-default">
    <div class="panel-heading d-flex justify-content-between align-items-center">
        <strong><?= Yii::t('TodoModule.base', 'Labels verwalten') ?></strong>
        <?= Html::a(Yii::t('TodoModule.base', 'Zurück'), $contentContainer->createUrl('/todo/task/index'), ['class' => 'btn btn-sm btn-default']) ?>
    </div>
    <div class="panel-body">
        <p class="text-muted"><?= Yii::t('TodoModule.base', 'Labels helfen, Aufgaben unabhängig von ihrer Aufgabenliste zu kennzeichnen und zu filtern.') ?></p>
        <?php foreach ($labels as $label): ?>
            <div class="well well-sm d-flex align-items-center gap-2" style="margin-bottom:8px;">
                <span style="width:16px;height:16px;border-radius:50%;background:<?= Html::encode($label->color) ?>;"></span>
                <?= Html::beginForm($contentContainer->createUrl('/todo/label/update', ['id' => $label->id]), 'post', ['class' => 'd-flex align-items-center gap-2 flex-grow-1']) ?>
                    <?= Html::textInput('name', $label->name, ['class' => 'form-control', 'maxlength' => 60, 'required' => true, 'aria-label' => Yii::t('TodoModule.base', 'Name des Labels')]) ?>
                    <?= Html::input('color', 'color', $label->color, ['class' => 'form-control', 'style' => 'width:60px;padding:3px;', 'aria-label' => Yii::t('TodoModule.base', 'Farbe des Labels')]) ?>
                    <?= Html::submitButton(Yii::t('TodoModule.base', 'Speichern'), ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::endForm() ?>
                <?= Html::beginForm($contentContainer->createUrl('/todo/label/delete', ['id' => $label->id]), 'post') ?>
                    <?= Html::submitButton('<i class="fa fa-trash" aria-hidden="true"></i>', ['class' => 'btn btn-sm btn-danger', 'title' => Yii::t('TodoModule.base', 'Löschen'), 'aria-label' => Yii::t('TodoModule.base', 'Label löschen'), 'data-confirm' => Yii::t('TodoModule.base', 'Label wirklich löschen? Die Aufgaben bleiben erhalten.')]) ?>
                <?= Html::endForm() ?>
            </div>
        <?php endforeach; ?>
        <?php if (!$labels): ?><p class="text-muted"><?= Yii::t('TodoModule.base', 'Noch keine Labels vorhanden.') ?></p><?php endif; ?>
        <hr>
        <h5><?= Yii::t('TodoModule.base', 'Neues Label') ?></h5>
        <?= Html::beginForm($contentContainer->createUrl('/todo/label/create'), 'post', ['class' => 'd-flex gap-2']) ?>
            <?= Html::textInput('name', '', ['class' => 'form-control', 'placeholder' => Yii::t('TodoModule.base', 'Name des neuen Labels'), 'maxlength' => 60, 'required' => true, 'aria-label' => Yii::t('TodoModule.base', 'Name des neuen Labels')]) ?>
            <?= Html::input('color', 'color', '#6f42c1', ['class' => 'form-control', 'style' => 'width:60px;padding:3px;', 'aria-label' => Yii::t('TodoModule.base', 'Farbe des Labels')]) ?>
            <?= Html::submitButton('<i class="fa fa-plus"></i> ' . Yii::t('TodoModule.base', 'Hinzufügen'), ['class' => 'btn btn-success']) ?>
        <?= Html::endForm() ?>
    </div>
</div>
