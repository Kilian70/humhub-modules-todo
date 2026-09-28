<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="panel panel-default">
    <div class="panel-heading d-flex justify-content-between align-items-center">
        <strong>Vorlage bearbeiten</strong>
        <?= Html::a('Zurück', $contentContainer->createUrl('/todo/template/index'), ['class' => 'btn btn-sm btn-light']) ?>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'title')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>
        <?= $form->field($model, 'priority')->dropDownList(['niedrig' => 'Niedrig', 'mittel' => 'Mittel', 'hoch' => 'Hoch']) ?>
        <?= $form->field($model, 'checklist_text')->textarea(['rows' => 8])->hint('Ein Checklistenpunkt pro Zeile.') ?>
        <?= Html::submitButton('Speichern', ['class' => 'btn btn-primary']) ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
