<?php

use humhub\helpers\Html;
use humhub\widgets\form\ActiveForm;

/* @var $model \humhub\modules\todo\models\SpaceSettingsForm */
/* @var $space \humhub\modules\space\models\Space */

$this->title = 'ToDo – Space-Einstellungen';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong>ToDo</strong> – <?= Html::encode($space->name) ?>
    </div>
    <div class="panel-body">
        <p class="text-muted">
            Diese Einstellungen gelten nur für diesen Space.
        </p>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'widgetEnabled')->checkbox() ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'widgetSortOrder')->input('number', ['min' => 0, 'max' => 10000]) ?>
                <div class="form-text text-muted"><?= Yii::t('TodoModule.base', 'Kleinere Zahl = weiter oben, grössere Zahl = weiter unten.') ?></div>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'widgetLimit')->input('number', ['min' => 1, 'max' => 20]) ?>
            </div>
        </div>

        <hr>
        <h4><?= Yii::t('TodoModule.base', 'Archiv') ?></h4>
        <?= $form->field($model, 'autoArchiveDays')->dropDownList([
            0 => Yii::t('TodoModule.base', 'Aus – nur manuell archivieren'),
            30 => Yii::t('TodoModule.base', 'Nach 30 Tagen'),
            60 => Yii::t('TodoModule.base', 'Nach 60 Tagen'),
            90 => Yii::t('TodoModule.base', 'Nach 90 Tagen'),
        ]) ?>
        <div class="form-text text-muted">
            <?= Yii::t('TodoModule.base', 'Es werden nur erledigte Aufgaben mit einem Abschlussdatum archiviert. Sie können im Archiv jederzeit wiederhergestellt werden.') ?>
        </div>

        <div class="mt-3">
            <?= Html::submitButton('Speichern', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
