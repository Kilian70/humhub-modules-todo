<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\user\widgets\UserPickerField;
use humhub\modules\todo\services\CalendarSyncService;
use humhub\modules\todo\services\UploadLimitService;
use humhub\modules\todo\models\TaskList;
use humhub\modules\todo\models\TaskLabel;

$uploadMaxFileSize = UploadLimitService::maxFileSize();
$uploadMaxTotalSize = UploadLimitService::maxTotalFileSize();
$uploadHelpId = 'todo-create-upload-help';

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
                    'role' => 'combobox',
                    'aria-autocomplete' => 'list',
                    'aria-expanded' => 'false',
                    'aria-controls' => $taskListMenuId,
                ]) ?>

                <div id="<?= $taskListMenuId ?>"
                     class="list-group position-absolute w-100 shadow-sm"
                     data-role="task-list-menu"
                     role="listbox"
                     style="display:none;z-index:1050;max-height:280px;overflow-y:auto;left:0;right:0;background:var(--hh-background-color-main,#fff);color:var(--hh-text-color-main,#333);">

                    <?php if (empty($taskLists)): ?>
                        <div class="list-group-item text-muted" data-role="empty-list-hint">
                            <?= Yii::t('TodoModule.base', 'Noch keine Aufgabenlisten vorhanden.') ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($taskLists as $taskList): ?>
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-center gap-2"
                                    data-role="task-list-option"
                                    role="option"
                                    aria-selected="false"
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
                            role="option"
                            aria-selected="false"
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

        function closeMenu() {
            menu.style.display = 'none';
            input.setAttribute('aria-expanded', 'false');
        }

        function visibleChoices() {
            return [...options, createButton].filter(function (option) {
                return option.style.display !== 'none';
            });
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
                option.setAttribute('aria-selected', normalizedName === query && query !== '' ? 'true' : 'false');

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
            input.setAttribute('aria-expanded', 'true');
        }

        input.addEventListener('focus', updateMenu);
        input.addEventListener('click', updateMenu);
        input.addEventListener('input', updateMenu);

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                input.value = option.dataset.name || '';
                closeMenu();
                input.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });

        createButton.addEventListener('click', function () {
            closeMenu();
            input.focus();
        });

        document.addEventListener('click', function (event) {
            if (!picker.contains(event.target)) {
                closeMenu();
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                updateMenu();
                visibleChoices()[0]?.focus();
            }
        });

        [...options, createButton].forEach(function (option) {
            option.addEventListener('keydown', function (event) {
                const choices = visibleChoices();
                const current = choices.indexOf(option);
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    const direction = event.key === 'ArrowDown' ? 1 : -1;
                    choices[(current + direction + choices.length) % choices.length]?.focus();
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeMenu();
                    input.focus();
                }
            });
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
            <label class="control-label" for="<?= Html::getInputId($model, 'uploadFiles') ?>"><?= Yii::t('TodoModule.base', 'Dateien hinzufügen') ?></label>
            <div class="mt-2">
                <?= $form->field($model, 'uploadFiles[]')
                    ->fileInput([
                        'multiple' => true,
                        'accept' => '.png,.jpg,.jpeg,.pdf,.doc,.docx,.xlsx',
                        'data-todo-upload-input' => true,
                        'data-max-file-size' => $uploadMaxFileSize,
                        'data-max-total-size' => $uploadMaxTotalSize,
                        'data-error-file' => Yii::t('TodoModule.base', 'Die Datei «{name}» ist größer als das erlaubte Limit von {size}.'),
                        'data-error-total' => Yii::t('TodoModule.base', 'Alle ausgewählten Dateien zusammen dürfen höchstens {size} groß sein.'),
                        'aria-describedby' => $uploadHelpId,
                    ])
                    ->label(false) ?>
                <div id="<?= $uploadHelpId ?>" class="small text-muted">
                    <?= Yii::t('TodoModule.base', 'Maximal {count} Dateien; pro Datei {fileSize}, zusammen {totalSize}. Erlaubt: PNG, JPG, PDF, DOC, DOCX und XLSX.', [
                        'count' => 10,
                        'fileSize' => Yii::$app->formatter->asShortSize($uploadMaxFileSize),
                        'totalSize' => Yii::$app->formatter->asShortSize($uploadMaxTotalSize),
                    ]) ?>
                </div>
                <div class="small text-danger" data-todo-upload-error aria-live="polite"></div>
            </div>
        </div>

        <?php $this->registerJs(<<<'JS'
document.querySelectorAll('[data-todo-upload-input]').forEach(function (input) {
    input.addEventListener('change', function () {
        const files = Array.from(input.files || []);
        const maxFile = Number(input.dataset.maxFileSize || 0);
        const maxTotal = Number(input.dataset.maxTotalSize || 0);
        const oversized = maxFile > 0 ? files.find(function (file) { return file.size > maxFile; }) : null;
        const total = files.reduce(function (sum, file) { return sum + file.size; }, 0);
        let message = '';
        if (oversized) {
            message = input.dataset.errorFile.replace('{name}', oversized.name).replace('{size}', formatBytes(maxFile));
        } else if (maxTotal > 0 && total > maxTotal) {
            message = input.dataset.errorTotal.replace('{size}', formatBytes(maxTotal));
        }
        input.setCustomValidity(message);
        const output = input.closest('.mb-4').querySelector('[data-todo-upload-error]');
        if (output) output.textContent = message;
    });
});
function formatBytes(bytes) {
    return (bytes / 1024 / 1024).toLocaleString(undefined, {maximumFractionDigits: 2}) + ' MB';
}
JS); ?>


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

        <?php $availableLabels = TaskLabel::findForSpace((int) $contentContainer->id); ?>
        <?php if ($availableLabels): ?>
            <fieldset class="form-group mt-2">
                <legend class="control-label" style="font-size:inherit;border:0;margin-bottom:5px;padding:0;"><?= Yii::t('TodoModule.base', 'Labels') ?></legend>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($availableLabels as $label): ?>
                        <label class="todo-label-choice">
                            <?= Html::activeCheckbox($model, 'label_ids[]', ['value' => $label->id, 'label' => false, 'uncheck' => null, 'checked' => in_array((int) $label->id, array_map('intval', (array) $model->label_ids), true)]) ?>
                            <span class="badge" style="background:<?= Html::encode($label->color) ?>;color:<?= Html::encode($label->textColor) ?>;"><?= Html::encode($label->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endif; ?>

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
                    <div class="form-text"><?= Yii::t('TodoModule.base', 'Für einen neuen Kalendereintrag fehlt dir das Kalender-Recht «Termin erstellen».') ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <!-- Zuständig -->
        <div class="mt-3">

            <strong><?= Yii::t('TodoModule.base', 'Zuständig') ?></strong>

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
