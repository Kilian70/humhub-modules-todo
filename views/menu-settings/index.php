<?php

use humhub\modules\todo\models\MenuSettingsForm;
use humhub\widgets\form\ActiveForm;

/* @var $model MenuSettingsForm */
$this->title = Yii::t('TodoModule.base', 'ToDo-Menü');
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>

<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Yii::t('TodoModule.base', 'ToDo-Menü') ?></strong></div>
    <div class="panel-body">
        <p class="text-muted"><?= Yii::t('TodoModule.base', 'Lege fest, ob und in welchem Bereich „Meine ToDos“ in deinem persönlichen Hauptmenü erscheint. Die genaue Reihenfolge hängt auch von den Menüpunkten anderer Module ab.') ?></p>

        <?php $form = ActiveForm::begin(['id' => 'todo-menu-settings-form', 'acknowledge' => true]); ?>
        <?= $form->field($model, 'menuVisible')->checkbox() ?>
        <?= $form->field($model, 'menuPosition')->dropDownList([
            'front' => Yii::t('TodoModule.base', 'Vorne – bevorzugt im vorderen Bereich'),
            'middle' => Yii::t('TodoModule.base', 'Mitte – bevorzugt im mittleren Bereich'),
            'back' => Yii::t('TodoModule.base', 'Hinten – bevorzugt im hinteren Bereich'),
        ]) ?>
        <hr>
        <h4><?= Yii::t('TodoModule.base', 'Benachrichtigungen') ?></h4>
        <p class="text-muted"><?= Yii::t('TodoModule.base', 'Diese Vorgabe gilt für alle Aufgaben, bei denen du keine eigene Auswahl getroffen hast.') ?></p>
        <?= $form->field($model, 'notificationDefault')->dropDownList([
            'all' => Yii::t('TodoModule.base', 'Alle ToDo-Benachrichtigungen'),
            'important' => Yii::t('TodoModule.base', 'Nur Kommentare und Zuweisungen'),
            'reminders' => Yii::t('TodoModule.base', 'Nur Erinnerungen'),
            'muted' => Yii::t('TodoModule.base', 'Stumm'),
        ]) ?>
        <button class="btn btn-primary" type="submit" data-ui-loader><?= Yii::t('TodoModule.base', 'Speichern') ?></button>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php $this->endContent() ?>
