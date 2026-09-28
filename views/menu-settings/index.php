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
        <p class="text-muted">Lege fest, ob und in welchem Bereich „Meine ToDos“ in deinem persönlichen Hauptmenü erscheint. Die genaue Reihenfolge hängt auch von den Menüpunkten anderer Module ab.</p>

        <?php $form = ActiveForm::begin(['id' => 'todo-menu-settings-form', 'acknowledge' => true]); ?>
        <?= $form->field($model, 'menuVisible')->checkbox() ?>
        <?= $form->field($model, 'menuPosition')->dropDownList([
            'front' => 'Vorne – bevorzugt im vorderen Bereich',
            'middle' => 'Mitte – bevorzugt im mittleren Bereich',
            'back' => 'Hinten – bevorzugt im hinteren Bereich',
        ]) ?>
        <button class="btn btn-primary" type="submit" data-ui-loader>Speichern</button>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php $this->endContent() ?>
