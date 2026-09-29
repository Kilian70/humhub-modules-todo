<?php

use humhub\helpers\Html;
use humhub\widgets\form\ActiveForm;

/* @var $model \humhub\modules\todo\models\SettingsForm */

$this->title = 'ToDo – Einstellungen';
?>

<div class="panel panel-default">
    <div class="panel-heading"><strong>ToDo</strong> – <?= Yii::t('TodoModule.base', 'Dashboard') ?></div>
    <div class="panel-body">
        <p class="text-muted">
            Hier wird nur das persönliche Dashboard-Widget konfiguriert.
            Das Space-Widget wird von jedem Space separat eingestellt.
        </p>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'dashboardWidgetEnabled')->checkbox() ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'dashboardWidgetSortOrder')->input('number', ['min' => 0, 'max' => 10000]) ?>
                <div class="form-text text-muted"><?= Yii::t('TodoModule.base', 'Kleinere Zahl = weiter oben, grössere Zahl = weiter unten.') ?></div>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'dashboardWidgetLimit')->input('number', ['min' => 1, 'max' => 20]) ?>
            </div>
        </div>

        <hr>
        <h4><?= Yii::t('TodoModule.base', 'Erinnerungen') ?></h4>
        <p class="text-muted">
            Jede Erinnerungsstufe wird pro Aufgabe nur einmal versendet. Wird das Fälligkeitsdatum geändert,
            werden die Stufen für diese Aufgabe zurückgesetzt.
        </p>
        <?= $form->field($model, 'remindersEnabled')->checkbox() ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'reminderDaysBefore')->input('number', ['min' => 0, 'max' => 30]) ?>
                <div class="form-text text-muted">0 deaktiviert die Vorwarnung; Standard sind 3 Tage.</div>
            </div>
        </div>
        <?= $form->field($model, 'upcomingReminderEnabled')->checkbox() ?>
        <?= $form->field($model, 'dueReminderEnabled')->checkbox() ?>
        <?= $form->field($model, 'overdueReminderEnabled')->checkbox() ?>

        <div class="mt-3">
            <?= Html::submitButton('Speichern', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
