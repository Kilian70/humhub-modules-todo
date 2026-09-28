<?php

use humhub\modules\todo\models\MenuSettingsForm;
use humhub\widgets\form\ActiveForm;

/* @var $model MenuSettingsForm */
$this->title = 'ToDo-Menü';
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>

<div class="panel panel-default">
    <div class="panel-heading"><strong>ToDo-Menü</strong></div>
    <div class="panel-body">
        <p class="text-muted">Lege fest, ob und an welcher Stelle „Meine ToDos“ in deinem persönlichen Hauptmenü erscheint.</p>

        <?php $form = ActiveForm::begin(['id' => 'todo-menu-settings-form', 'acknowledge' => true]); ?>
        <?= $form->field($model, 'menuVisible')->checkbox() ?>
        <?= $form->field($model, 'menuPosition')->dropDownList([
            'front' => 'Vorne – direkt nach der Übersicht',
            'middle' => 'Mitte',
            'back' => 'Hinten',
        ]) ?>
        <button class="btn btn-primary" type="submit" data-ui-loader>Speichern</button>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php $this->endContent() ?>
