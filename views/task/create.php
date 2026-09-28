<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\user\widgets\UserPickerField;
use humhub\modules\todo\services\CalendarSyncService;
use humhub\modules\todo\models\TaskList;

?>

<div class="panel panel-default">

    <!-- HEADER -->
    <div class="panel-heading d-flex justify-content-between align-items-center">

        <strong><?= $parentTask ? Yii::t('TodoModule.base', 'Neue Unteraufgabe') : Yii::t('TodoModule.base', 'Neue Aufgabe') ?></strong>

        <div>
            <?= Html::a(
                Yii::t('TodoModule.base', 'Zurück'),
                $parentTask
                    ? $contentContainer->createUrl('/todo/task/view', ['id' => $parentTask->id])
                    : $contentContainer->createUrl('/todo/task/index'),
                ['class' => 'btn btn-sm btn-light']
            ) ?>
        </div>

    </div>


    <!-- BODY -->
    <div class="panel-body">

        <?php $form = ActiveForm::begin([
            'options' => [
                'data-pjax' => 0,
                'enctype' => 'multipart/form-data'
            ]
        ]); ?>

        <?= Html::activeHiddenInput($model, 'parent_task_id') ?>

        <?php if ($parentTask): ?>
            <div class="alert alert-info">
                <?= Yii::t('TodoModule.base', 'Unteraufgabe von') ?> <?= Html::a(
                    Html::encode($parentTask->title),
                    $contentContainer->createUrl('/todo/task/view', ['id' => $parentTask->id])
                ) ?>
            </div>
        <?php endif; ?>


        <?= $form->field($model, 'title')->textInput([
            'placeholder' => Yii::t('TodoModule.base', 'Titel der Aufgabe')
        ]) ?>

        <?php
        $taskLists = TaskList::findForSpace((int) $contentContainer->id);
        $taskListInputId = 'todo-task-list-input-create';
        $taskListMenuId = 'todo-task-list-menu-create';
        ?>

        <div class="form-group">
            <label class="control-label" for="<?= $taskListInputId ?>"><?= Yii::t('TodoModule.base', 'Aufgabenliste') ?></label>

            <div class="position-relative todo-task-list-picker" data-todo-task-list-picker>
                <?= Html::activeTextInput($model, 'task_list_name', [
                    'id' => $taskListInputId,
                    'class' => 'form-control',
                    'placeholder' => Yii::t('TodoModule.base', 'Liste auswählen oder neuen Namen eingeben'),
                    'autocomplete' => 'off',
                    'data-role' => 'task-list-input',
                ]) ?>

                <div id="<?= $taskListMenuId ?>"
                     class="list-group position-absolute w-100 shadow-sm bg-white"
                     data-role="task-list-menu"
                     style="display:none;z-index:1050;max-height:280px;overflow-y:auto;left:0;right:0;">

                    <?php if (empty($taskLists)): ?>
                        <div class="list-group-item text-muted" data-role="empty-list-hint">
                            <?= Yii::t('TodoModule.base', 'Noch keine Aufgabenlisten vorhanden.') ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($taskLists as $taskList): ?>
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-center gap-2"
                                    data-role="task-list-option"
                                    data-name="<?= Html::encode($taskList->name) ?>">
                                <span aria-hidden="true"
                                      style="display:inline-block;width:14px;height:14px;border-radius:4px;flex:0 0 14px;background:<?= Html::encode($taskList->color ?: '#6c757d') ?>;"></span>
                                <span><?= Html::encode($taskList->name) ?></span>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <button type="button"
                            class="list-group-item list-group-item-action text-primary"
                            data-role="task-list-create"
                            style="display:none;">
                        <i class="fa fa-plus"></i>
                        <span data-role="task-list-create-label"><?= Yii::t('TodoModule.base', 'Neue Aufgabenliste erstellen') ?></span>
                    </button>
                </div>
            </div>

            <div class="help-block">
                <?= Yii::t('TodoModule.base', 'Bestehende Liste auswählen oder einen neuen Namen eingeben. Neue Listen werden beim Speichern automatisch angelegt.') ?>
            </div>
        </div>

        <?php
        $taskListPickerJs = <<<'JS'
(function () {
    document.querySelectorAll('[data-todo-task-list-picker]').forEach(function (picker) {
        if (picker.dataset.initialized === '1') {
            return;
        }
        picker.dataset.initialized = '1';

        const input = picker.querySelector('[data-role="task-list-input"]');
        const menu = picker.querySelector('[data-role="task-list-menu"]');
        const options = Array.from(picker.querySelectorAll('[data-role="task-list-option"]'));
        const createButton = picker.querySelector('[data-role="task-list-create"]');
        const createLabel = picker.querySelector('[data-role="task-list-create-label"]');

        function normalize(value) {
            return (value || '').trim().toLocaleLowerCase();
        }

        function updateMenu() {
            const query = normalize(input.value);
            let exactMatch = false;
            let visibleCount = 0;

            options.forEach(function (option) {
                const name = option.dataset.name || '';
                const normalizedName = normalize(name);
                const visible = query === '' || normalizedName.includes(query);
                option.style.display = visible ? '' : 'none';

                if (visible) {
                    visibleCount++;
                }
                if (normalizedName === query && query !== '') {
                    exactMatch = true;
                }
            });

            if (query !== '' && !exactMatch) {
                createButton.style.display = '';
                createLabel.textContent = 'Aufgabenliste «' + input.value.trim() + '» erstellen';
            } else {
                createButton.style.display = 'none';
            }

            menu.style.display = '';
        }

        input.addEventListener('focus', updateMenu);
        input.addEventListener('click', updateMenu);
        input.addEventListener('input', updateMenu);

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                input.value = option.dataset.name || '';
                menu.style.display = 'none';
                input.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });

        createButton.addEventListener('click', function () {
            menu.style.display = 'none';
            input.focus();
        });

        document.addEventListener('click', function (event) {
            if (!picker.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                menu.style.display = 'none';
            }
        });
    });
})();
JS;
        $this->registerJs($taskListPickerJs);
        ?>


        <?= $form->field($model, 'description')->textarea([
            'rows' => 4
        ]) ?>


        <!-- Upload -->
        <div class="mb-4">
            <strong>Dateien hinzufügen</strong>
            <div class="mt-2">
                <?= $form->field($model, 'uploadFiles[]')
                    ->fileInput(['multiple' => true])
                    ->label(false) ?>
            </div>
        </div>


        <!-- META GRID -->
        <div class="row">

            <div class="col-md-4">
                <?= $form->field($model, 'priority')->dropDownList([
                    'niedrig' => Yii::t('TodoModule.base', 'Niedrig'),
                    'mittel' => Yii::t('TodoModule.base', 'Mittel'),
                    'hoch' => Yii::t('TodoModule.base', 'Hoch'),
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'status')->dropDownList([
                    'offen' => Yii::t('TodoModule.base', 'Offen'),
                    'in_bearbeitung' => Yii::t('TodoModule.base', 'In Bearbeitung'),
                    'geschlossen' => Yii::t('TodoModule.base', 'Geschlossen'),
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'due_date')->input('date') ?>
            </div>

        </div>

        <div class="row mt-2">
            <div class="col-md-4">
                <?= $form->field($model, 'recurrence_type')->dropDownList([
                    '' => Yii::t('TodoModule.base', 'Keine Wiederholung'),
                    'daily' => Yii::t('TodoModule.base', 'Täglich'),
                    'weekly' => Yii::t('TodoModule.base', 'Wöchentlich'),
                    'monthly' => Yii::t('TodoModule.base', 'Monatlich'),
                    'yearly' => Yii::t('TodoModule.base', 'Jährlich'),
                ], ['id' => 'recurrence-type']) ?>
            </div>
            <div class="col-md-4 recurrence-options">
                <?= $form->field($model, 'recurrence_interval')->input('number', ['min' => 1, 'max' => 365])
                    ->hint('Zum Beispiel 2 = alle zwei Wochen') ?>
            </div>
            <div class="col-md-4 recurrence-options">
                <?= $form->field($model, 'recurrence_end_date')->input('date')->hint('Optional') ?>
            </div>
        </div>
        <?php $this->registerJs("(function(){var type=document.getElementById('recurrence-type');if(!type)return;function toggle(){document.querySelectorAll('.recurrence-options').forEach(function(el){el.style.display=type.value?'':'none';});}type.addEventListener('change',toggle);toggle();})();"); ?>

        <?php if (CalendarSyncService::isAvailable($contentContainer)): ?>
            <div class="mt-2">
                <?= $form->field($model, 'sync_to_calendar')->checkbox([
                    'label' => Yii::t('TodoModule.base', 'Fälligkeit als ganztägigen Termin im Kalender eintragen'),
                    'disabled' => !$model->calendar_entry_id && !CalendarSyncService::canCreate($contentContainer),
                ]) ?>
                <?php if (!$model->calendar_entry_id && !CalendarSyncService::canCreate($contentContainer)): ?>
                    <div class="form-text">Für einen neuen Kalendereintrag fehlt dir das Kalender-Recht «Termin erstellen».</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <!-- Zuständig -->
        <div class="mt-3">

            <strong>Zuständig</strong>

            <div class="mt-2">

                <?= $form->field($model, 'user_ids')
                    ->widget(UserPickerField::class)
                    ->label(false) ?>

            </div>

        </div>


        <!-- FOOTER -->
        <div class="mt-4 text-end">

            <?= Html::submitButton(
                Yii::t('TodoModule.base', 'Speichern'),
                ['class' => 'btn btn-primary']
            ) ?>

        </div>


        <?php ActiveForm::end(); ?>

    </div>

</div>
