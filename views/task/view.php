<?php

use yii\helpers\Html;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\user\models\User;
use humhub\modules\todo\services\CalendarSyncService;
use humhub\modules\todo\services\TaskNotificationPreferenceService;
use humhub\modules\todo\services\UploadLimitService;
use humhub\modules\comment\widgets\Comments;

use humhub\modules\space\models\Membership;
use humhub\modules\user\widgets\UserPickerField;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

?>

<?php
$canManageTask = $task->canManage();
$isActiveTask = $task->deleted_at === null && $task->archived_at === null;
$canEditTask = $isActiveTask && $canManageTask;
$canWorkOnTask = $isActiveTask && $task->canWorkOn();
$canCreateTask = $contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\CreateTasks());
$notificationMode = TaskNotificationPreferenceService::getOverrideMode((int) $task->id, (int) Yii::$app->user->id);
$notificationDefault = TaskNotificationPreferenceService::getDefaultMode((int) Yii::$app->user->id);
$uploadMaxFileSize = UploadLimitService::maxFileSize();
?>

<div class="panel panel-default">

    <!-- HEADER -->
    <div class="panel-heading d-flex justify-content-between align-items-center">

        <strong><?= Html::encode($task->title) ?></strong>

        <div>

            <?= Html::a(
                Yii::t('TodoModule.base', 'Zurück'),
                $contentContainer->createUrl('/todo/task/index', $task->archived_at ? ['archive' => 1] : []),
                ['class' => 'btn btn-sm btn-light']
            ) ?>

            <?php if ($canEditTask): ?>
                <?= Html::a(
                    Yii::t('TodoModule.base', 'Bearbeiten'),
                    $contentContainer->createUrl('/todo/task/update', ['id' => $task->id]),
                    ['class' => 'btn btn-sm btn-primary']
                ) ?>
            <?php endif; ?>

            <?php if ($canManageTask && $task->archived_at && $task->deleted_at === null): ?>
                <?= Html::beginForm($contentContainer->createUrl('/todo/task/restore', ['id' => $task->id]), 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('<i class="fa fa-undo"></i> ' . Yii::t('TodoModule.base', 'Wiederherstellen'), ['class' => 'btn btn-sm btn-success']) ?>
                <?= Html::endForm() ?>
            <?php elseif ($canEditTask && $task->status === 'geschlossen'): ?>
                <?= Html::beginForm($contentContainer->createUrl('/todo/task/archive', ['id' => $task->id]), 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('<i class="fa fa-archive"></i> ' . Yii::t('TodoModule.base', 'Archivieren'), [
                    'class' => 'btn btn-sm btn-default',
                    'data-confirm' => Yii::t('TodoModule.base', 'Aufgabe archivieren?'),
                ]) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>

            <?php if ($canCreateTask): ?>
                <?= Html::beginForm(
                    $contentContainer->createUrl('/todo/task/duplicate', ['id' => $task->id]),
                    'post',
                    ['class' => 'd-inline']
                ) ?>
                <?= Html::submitButton('<i class="fa fa-copy"></i> Duplizieren', [
                    'class' => 'btn btn-sm btn-light',
                    'data-confirm' => Yii::t('TodoModule.base', 'Diese Aufgabe als neue offene Aufgabe duplizieren?'),
                ]) ?>
                <?= Html::endForm() ?>

                <?= Html::beginForm(
                    $contentContainer->createUrl('/todo/template/save-from-task', ['id' => $task->id]),
                    'post',
                    ['class' => 'd-inline']
                ) ?>
                <?= Html::submitButton('<i class="fa fa-bookmark"></i> Als Vorlage', [
                    'class' => 'btn btn-sm btn-light',
                    'data-confirm' => Yii::t('TodoModule.base', 'Diese Aufgabe als neue Vorlage speichern?'),
                ]) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>

        </div>

    </div>


    <!-- BODY -->
    <div class="panel-body">


        <!-- BESCHREIBUNG -->
        <?php if (!empty($task->description)): ?>

            <div class="mb-4">

                <strong><?= Yii::t('TodoModule.base', 'Beschreibung') ?></strong>

                <div class="mt-1">
                    <?= nl2br(Html::encode($task->description)) ?>
                </div>

            </div>

        <?php endif; ?>


        <?php
        $statusClass = match ($task->status) {
            'in_bearbeitung' => 'badge bg-info text-dark',
            'geschlossen' => 'badge bg-secondary',
            default => 'badge bg-light text-dark border',
        };
        $statusLabel = match ($task->status) {
            'in_bearbeitung' => 'IN BEARBEITUNG',
            'geschlossen' => 'GESCHLOSSEN',
            default => 'OFFEN',
        };
        ?>
        <div class="mb-3">
            <?php if ($task->archived_at): ?>
                <span class="badge bg-dark"><i class="fa fa-archive"></i> <?= Yii::t('TodoModule.base', 'ARCHIVIERT') ?></span>
            <?php endif; ?>
            <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
            <?php if ($task->getOpenBlockingTasks()->exists()): ?>
                <span class="badge bg-warning text-dark ms-1"><i class="fa fa-lock"></i> <?= Yii::t('TodoModule.base', 'BLOCKIERT') ?></span>
            <?php endif; ?>
            <?php foreach ($task->taskLabels as $label): ?>
                <span class="badge ms-1" style="background:<?= Html::encode($label->color) ?>;color:<?= Html::encode($label->textColor) ?>;"><?= Html::encode($label->name) ?></span>
            <?php endforeach; ?>
            <?php if ($task->status === 'geschlossen' && $task->closed_at): ?>
                <span class="small text-muted ms-2">
                    Geschlossen
                    <?php if ($task->closedByUser): ?>von <?= Html::encode($task->closedByUser->displayName) ?><?php endif; ?>
                    am <?= Yii::$app->formatter->asDatetime($task->closed_at) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (in_array($task->recurrence_type, ['daily', 'weekly', 'monthly', 'yearly'], true)): ?>
            <?php
            $singleNames = ['daily' => 'Jeden Tag', 'weekly' => 'Jede Woche', 'monthly' => 'Jeden Monat', 'yearly' => 'Jedes Jahr'];
            $pluralNames = ['daily' => 'Tage', 'weekly' => 'Wochen', 'monthly' => 'Monate', 'yearly' => 'Jahre'];
            $recurrenceText = (int) $task->recurrence_interval === 1
                ? $singleNames[$task->recurrence_type]
                : 'Alle ' . (int) $task->recurrence_interval . ' ' . $pluralNames[$task->recurrence_type];
            ?>
            <div class="mb-3 small text-muted">
                <i class="fa fa-repeat"></i>
                <?= Html::encode($recurrenceText) ?>
                <?php if ($task->recurrence_end_date): ?>
                    · bis <?= Yii::$app->formatter->asDate($task->recurrence_end_date) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($task->taskList): ?>
            <div class="mb-3 small">
                <span class="badge" style="background-color: <?= Html::encode($task->taskList->color) ?>; color:#fff;">
                    <?= Html::encode($task->taskList->name) ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($task->parentTask): ?>
            <div class="mb-3 small">
                <i class="fa fa-level-up"></i>
                Hauptaufgabe:
                <?= Html::a(
                    Html::encode($task->parentTask->title),
                    $contentContainer->createUrl('/todo/task/view', ['id' => $task->parentTask->id])
                ) ?>
            </div>
        <?php endif; ?>

        <?php $blockingTasks = $task->blockingTasks; ?>
        <?php if (!empty($blockingTasks) || $canEditTask): ?>
            <details class="card mb-4" <?= !empty($blockingTasks) ? 'open' : '' ?>>
                <summary class="card-header py-2" style="cursor:pointer;list-style:none;">
                    <strong><i class="fa fa-link"></i> <?= Yii::t('TodoModule.base', 'Voraussetzungen') ?></strong>
                    <?php if ($task->getOpenBlockingTasks()->exists()): ?>
                        <span class="badge bg-warning text-dark ms-1">blockiert</span>
                    <?php endif; ?>
                </summary>
                <div class="card-body p-2">
                    <?php if (empty($blockingTasks)): ?>
                        <p class="text-muted mb-2"><?= Yii::t('TodoModule.base', 'Keine Voraussetzungen festgelegt.') ?></p>
                    <?php else: ?>
                        <div class="list-group mb-2">
                            <?php foreach ($blockingTasks as $blockingTask): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <?= Html::a(
                                            Html::encode($blockingTask->title),
                                            $contentContainer->createUrl('/todo/task/view', ['id' => $blockingTask->id])
                                        ) ?>
                                        <span class="badge bg-<?= $blockingTask->status === 'geschlossen' ? 'success' : 'warning text-dark' ?> ms-1">
                                            <?= $blockingTask->status === 'geschlossen' ? 'Erledigt' : 'Offen' ?>
                                        </span>
                                    </span>
                                    <?php if ($canEditTask): ?>
                                        <?= Html::beginForm($contentContainer->createUrl('/todo/task/dependency-remove', [
                                            'id' => $task->id,
                                            'blockingTaskId' => $blockingTask->id,
                                        ]), 'post', ['class' => 'd-inline']) ?>
                                        <?= Html::submitButton('<i class="fa fa-times"></i>', [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => Yii::t('TodoModule.base', 'Voraussetzung entfernen'),
                                            'aria-label' => Yii::t('TodoModule.base', 'Voraussetzung entfernen'),
                                        ]) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($canEditTask && !empty($dependencyCandidates)): ?>
                        <?= Html::beginForm($contentContainer->createUrl('/todo/task/dependency-add', ['id' => $task->id]), 'post', [
                            'class' => 'd-flex gap-2 align-items-end',
                        ]) ?>
                        <div class="flex-grow-1">
                            <label class="control-label" for="todo-blocking-task"><?= Yii::t('TodoModule.base', 'Aufgabe auswählen') ?></label>
                            <?= Html::dropDownList(
                                'blocking_task_id',
                                null,
                                ArrayHelper::map($dependencyCandidates, 'id', 'title'),
                                ['id' => 'todo-blocking-task', 'class' => 'form-control']
                            ) ?>
                        </div>
                        <?= Html::submitButton('<i class="fa fa-plus"></i> Hinzufügen', ['class' => 'btn btn-sm btn-primary mb-1']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </details>
        <?php endif; ?>

        <?php
        $subtasks = $task->subtasks;
        $completedSubtasks = count(array_filter($subtasks, static fn($subtask) => $subtask->status === 'geschlossen'));
        $canCreateSubtasks = $canCreateTask && $isActiveTask;
        ?>
        <details class="card mb-4" <?= empty($subtasks) ? '' : 'open' ?>>
            <summary class="card-header py-2" style="cursor:pointer;list-style:none;">
                <span class="d-flex justify-content-between align-items-center">
                    <strong><i class="fa fa-sitemap"></i> <?= Yii::t('TodoModule.base', 'Unteraufgaben') ?></strong>
                    <span class="badge bg-secondary"><?= $completedSubtasks ?> / <?= count($subtasks) ?></span>
                </span>
            </summary>
            <div class="card-body p-2">
                <?php if (empty($subtasks)): ?>
                    <p class="text-muted mb-2"><?= Yii::t('TodoModule.base', 'Noch keine Unteraufgaben vorhanden.') ?></p>
                <?php else: ?>
                    <div class="list-group mb-2">
                        <?php foreach ($subtasks as $subtask): ?>
                            <?php
                            $subtaskStatus = match ($subtask->status) {
                                'geschlossen' => ['success', 'Erledigt'],
                                'in_bearbeitung' => ['info', 'In Bearbeitung'],
                                default => ['secondary', 'Offen'],
                            };
                            ?>
                            <?= Html::a(
                                '<span>' . Html::encode($subtask->title) . '</span>'
                                . '<span class="ms-2">'
                                . ($subtask->due_date ? '<small class="text-muted me-2"><i class="fa fa-calendar"></i> ' . Yii::$app->formatter->asDate($subtask->due_date) . '</small>' : '')
                                . '<span class="badge bg-' . $subtaskStatus[0] . '">' . $subtaskStatus[1] . '</span></span>',
                                $contentContainer->createUrl('/todo/task/view', ['id' => $subtask->id]),
                                ['class' => 'list-group-item list-group-item-action d-flex justify-content-between align-items-center']
                            ) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($canCreateSubtasks): ?>
                    <?= Html::a(
                        '<i class="fa fa-plus"></i> Unteraufgabe erstellen',
                        $contentContainer->createUrl('/todo/task/create', ['parent_id' => $task->id]),
                        ['class' => 'btn btn-sm btn-primary']
                    ) ?>
                <?php endif; ?>
            </div>
        </details>

        <!-- CHECKLISTE -->
        <?php
        $canEditChecklist = $canWorkOnTask;
        $checklistItems = $task->checklistItems;
        $doneCount = count(array_filter($checklistItems, static fn($item) => (bool) $item->is_done));
        $spaceMembers = User::find()
            ->innerJoin('space_membership sm', 'sm.user_id = user.id')
            ->where([
                'sm.space_id' => $contentContainer->id,
                'sm.status' => Membership::STATUS_MEMBER,
            ])
            ->orderBy(['user.username' => SORT_ASC])
            ->all();
        $spaceUserSearchUrl = Url::to(['/user/search/json', 'space_id' => $contentContainer->id]);
        $calendarAvailable = CalendarSyncService::isAvailable($contentContainer);
        $calendarCanCreate = CalendarSyncService::canCreate($contentContainer);
        ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <strong><?= Yii::t('TodoModule.base', 'Checkliste') ?></strong>
                <?php if (!empty($checklistItems)): ?>
                    <span class="badge bg-secondary"><?= $doneCount ?> / <?= count($checklistItems) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body p-2">
                <?php if (empty($checklistItems)): ?>
                    <p class="text-muted mb-2"><?= Yii::t('TodoModule.base', 'Noch keine Checklistenpunkte vorhanden.') ?></p>
                <?php else: ?>
                    <div class="mb-2">
                        <?php foreach ($checklistItems as $index => $item): ?>
                            <div class="list-group-item <?= $item->is_done ? 'list-group-item-success' : '' ?> border rounded px-2 py-2 mb-1">
                                <div class="d-flex align-items-start gap-2">
                                    <?php if ($canEditChecklist): ?>
                                        <?= Html::beginForm($contentContainer->createUrl('/todo/task/checklist-toggle', ['id' => $task->id, 'itemId' => $item->id]), 'post', ['class' => 'm-0']) ?>
                                        <?= Html::submitButton($item->is_done ? '<i class="fa fa-check-square"></i>' : '<i class="fa fa-square-o"></i>', [
                                            'class' => 'btn btn-sm ' . ($item->is_done ? 'btn-success' : 'btn-outline-secondary'),
                                            'title' => $item->is_done ? Yii::t('TodoModule.base', 'Wieder öffnen') : Yii::t('TodoModule.base', 'Abhaken'),
                                            'aria-label' => $item->is_done ? Yii::t('TodoModule.base', 'Wieder öffnen') : Yii::t('TodoModule.base', 'Abhaken'),
                                        ]) ?>
                                        <?= Html::endForm() ?>
                                    <?php else: ?>
                                        <span><?= $item->is_done ? '✓' : '○' ?></span>
                                    <?php endif; ?>

                                    <div class="flex-grow-1 min-width-0">
                                        <div class="<?= $item->is_done ? 'text-decoration-line-through text-muted' : '' ?>">
                                            <?= Html::encode($item->title) ?>
                                        </div>

                                        <?php if ($item->assignedUsers || $item->due_date || ($item->is_done && $item->completedByUser && $item->completed_at)): ?>
                                            <div class="small text-muted mt-1">
                                                <?php if ($item->assignedUsers): ?>
                                                    <span class="me-3"><i class="fa fa-user"></i>
                                                        <?= Html::encode(implode(', ', array_map(static fn($user) => $user->displayName, $item->assignedUsers))) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($item->due_date): ?>
                                                    <span class="me-3"><i class="fa fa-calendar"></i> Termin: <?= Yii::$app->formatter->asDate($item->due_date) ?><?= $item->sync_to_calendar ? ' · Kalender' : '' ?></span>
                                                <?php endif; ?>
                                                <?php if ($item->is_done && $item->completedByUser && $item->completed_at): ?>
                                                    <span><i class="fa fa-check"></i> Erledigt von <?= Html::encode($item->completedByUser->displayName) ?> am <?= Yii::$app->formatter->asDate($item->completed_at) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($canEditChecklist): ?>
                                            <details class="mt-1">
                                                <summary class="small text-muted" style="cursor:pointer"><?= Yii::t('TodoModule.base', 'Bearbeiten') ?></summary>
                                                <?= Html::beginForm($contentContainer->createUrl('/todo/task/checklist-edit', ['id' => $task->id, 'itemId' => $item->id]), 'post', ['class' => 'row g-2 mt-1 align-items-end']) ?>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1"><?= Yii::t('TodoModule.base', 'Checklistenpunkt') ?></label>
                                                    <?= Html::textInput('title', $item->title, ['class' => 'form-control form-control-sm', 'maxlength' => 255, 'required' => true]) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1"><?= Yii::t('TodoModule.base', 'Zuständig') ?></label>
                                                    <?= UserPickerField::widget([
                                                        'name' => 'assigned_user_guids',
                                                        'selection' => $item->assignedUsers,
                                                        'defaultResults' => $spaceMembers,
                                                        'url' => $spaceUserSearchUrl,
                                                        'placeholder' => Yii::t('TodoModule.base', 'Benutzer auswählen'),
                                                        'placeholderMore' => Yii::t('TodoModule.base', 'Benutzer hinzufügen'),
                                                        'minInput' => 1,
                                                    ]) ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small mb-1"><?= Yii::t('TodoModule.base', 'Termin') ?></label>
                                                    <?= Html::input('date', 'due_date', $item->due_date, ['class' => 'form-control form-control-sm']) ?>
                                                    <?php if ($calendarAvailable): ?>
                                                        <div class="form-check mt-1">
                                                            <?= Html::checkbox('sync_to_calendar', (bool) $item->sync_to_calendar, [
                                                                'value' => 1,
                                                                'class' => 'form-check-input',
                                                                'id' => 'calendar-item-' . $item->id,
                                                                'disabled' => !$item->calendar_entry_id && !$calendarCanCreate,
                                                            ]) ?>
                                                            <label class="form-check-label small" for="calendar-item-<?= $item->id ?>"><?= Yii::t('TodoModule.base', 'Kalender') ?></label>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <?= Html::submitButton('Speichern', ['class' => 'btn btn-sm btn-primary w-100']) ?>
                                                </div>
                                                <?= Html::endForm() ?>
                                            </details>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($canEditChecklist): ?>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <?php if ($index > 0): ?>
                                                <?= Html::beginForm($contentContainer->createUrl('/todo/task/checklist-move', ['id' => $task->id, 'itemId' => $item->id, 'direction' => 'up']), 'post', ['class' => 'm-0']) ?>
                                                <?= Html::submitButton('<i class="fa fa-arrow-up"></i>', ['class' => 'btn btn-sm btn-outline-secondary', 'title' => Yii::t('TodoModule.base', 'Nach oben'), 'aria-label' => Yii::t('TodoModule.base', 'Nach oben')]) ?>
                                                <?= Html::endForm() ?>
                                            <?php endif; ?>
                                            <?php if ($index < count($checklistItems) - 1): ?>
                                                <?= Html::beginForm($contentContainer->createUrl('/todo/task/checklist-move', ['id' => $task->id, 'itemId' => $item->id, 'direction' => 'down']), 'post', ['class' => 'm-0']) ?>
                                                <?= Html::submitButton('<i class="fa fa-arrow-down"></i>', ['class' => 'btn btn-sm btn-outline-secondary', 'title' => Yii::t('TodoModule.base', 'Nach unten'), 'aria-label' => Yii::t('TodoModule.base', 'Nach unten')]) ?>
                                                <?= Html::endForm() ?>
                                            <?php endif; ?>
                                            <?= Html::beginForm($contentContainer->createUrl('/todo/task/checklist-delete', ['id' => $task->id, 'itemId' => $item->id]), 'post', ['class' => 'm-0']) ?>
                                            <?= Html::submitButton('<i class="fa fa-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'title' => Yii::t('TodoModule.base', 'Löschen'), 'aria-label' => Yii::t('TodoModule.base', 'Löschen'), 'data-confirm' => Yii::t('TodoModule.base', 'Checklistenpunkt wirklich löschen?')]) ?>
                                            <?= Html::endForm() ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($canEditChecklist): ?>
                    <?= Html::beginForm($contentContainer->createUrl('/todo/task/checklist-add', ['id' => $task->id]), 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-md-4">
                        <label class="form-label small mb-1"><?= Yii::t('TodoModule.base', 'Checklistenpunkt') ?></label>
                        <?= Html::textInput('title', '', ['class' => 'form-control', 'placeholder' => Yii::t('TodoModule.base', 'Neuer Checklistenpunkt …'), 'maxlength' => 255, 'required' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1"><?= Yii::t('TodoModule.base', 'Zuständig') ?></label>
                        <?= UserPickerField::widget([
                            'name' => 'assigned_user_guids',
                            'selection' => [],
                            'defaultResults' => $spaceMembers,
                            'url' => $spaceUserSearchUrl,
                            'placeholder' => Yii::t('TodoModule.base', 'Benutzer auswählen'),
                            'placeholderMore' => Yii::t('TodoModule.base', 'Benutzer hinzufügen'),
                            'minInput' => 1,
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1"><?= Yii::t('TodoModule.base', 'Termin') ?></label>
                        <?= Html::input('date', 'due_date', '', ['class' => 'form-control']) ?>
                        <?php if ($calendarAvailable): ?>
                            <div class="form-check mt-1">
                                <?= Html::checkbox('sync_to_calendar', false, [
                                    'value' => 1,
                                    'class' => 'form-check-input',
                                    'id' => 'calendar-item-new',
                                    'disabled' => !$calendarCanCreate,
                                ]) ?>
                                <label class="form-check-label small" for="calendar-item-new"><?= Yii::t('TodoModule.base', 'Kalender') ?></label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::submitButton('<i class="fa fa-plus"></i> Hinzufügen', ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $openChecklistCount = 0;
        foreach ($task->checklistItems as $checklistItem) {
            if (!$checklistItem->is_done) {
                $openChecklistCount++;
            }
        }
        ?>

        <!-- META / DIREKT BEARBEITBAR -->
        <div class="row g-3">

            <div class="col-md-4">
                <strong><?= Yii::t('TodoModule.base', 'Status') ?></strong><?php if ($canWorkOnTask): ?> <span class="text-muted small"><i class="fa fa-pencil"></i></span><?php endif; ?><br>

                <?php if ($canWorkOnTask): ?>
                    <?= Html::beginForm(
                        $contentContainer->createUrl('/todo/task/quick-update', ['id' => $task->id]),
                        'post',
                        ['class' => 'mt-1']
                    ) ?>
                    <?= Html::hiddenInput('field', 'status') ?>
                    <?= Html::hiddenInput('confirm_open_checklist', '0', ['class' => 'js-confirm-open-checklist']) ?>
                    <?= Html::dropDownList(
                        'value',
                        $task->status,
                        [
                            'offen' => Yii::t('TodoModule.base', 'Offen'),
                            'in_bearbeitung' => Yii::t('TodoModule.base', 'In Bearbeitung'),
                            'geschlossen' => Yii::t('TodoModule.base', 'Geschlossen'),
                        ],
                        [
                            'class' => 'form-control input-sm',
                            'style' => 'max-width:190px;height:34px;cursor:pointer;',
                            'onchange' => 'return todoConfirmClose(this, ' . (int) $openChecklistCount . ');',
                            'aria-label' => Yii::t('TodoModule.base', 'Status ändern'),
                            'data-current-status' => $task->status,
                        ]
                    ) ?>
                    <?= Html::endForm() ?>
                <?php else: ?>
                    <?php
                    $statusText = match ($task->status) {
                        'in_bearbeitung' => Yii::t('TodoModule.base', 'In Bearbeitung'),
                        'geschlossen' => Yii::t('TodoModule.base', 'Geschlossen'),
                        default => Yii::t('TodoModule.base', 'Offen'),
                    };
                    ?>
                    <?= Html::encode($statusText) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <strong><?= Yii::t('TodoModule.base', 'Priorität') ?></strong><?php if ($canEditTask): ?> <span class="text-muted small"><i class="fa fa-pencil"></i></span><?php endif; ?><br>

                <?php if ($canEditTask): ?>
                    <?= Html::beginForm(
                        $contentContainer->createUrl('/todo/task/quick-update', ['id' => $task->id]),
                        'post',
                        ['class' => 'mt-1']
                    ) ?>
                    <?= Html::hiddenInput('field', 'priority') ?>
                    <?= Html::dropDownList(
                        'value',
                        $task->priority,
                        [
                            'niedrig' => 'Niedrig',
                            'mittel' => 'Mittel',
                            'hoch' => 'Hoch',
                        ],
                        [
                            'class' => 'form-control input-sm',
                            'style' => 'max-width:190px;height:34px;cursor:pointer;',
                            'onchange' => 'this.form.submit();',
                            'aria-label' => Yii::t('TodoModule.base', 'Priorität ändern'),
                        ]
                    ) ?>
                    <?= Html::endForm() ?>
                <?php else: ?>
                    <?= Html::encode(ucfirst($task->priority)) ?>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <strong><?= Yii::t('TodoModule.base', 'Fällig') ?></strong><?php if ($canEditTask): ?> <span class="text-muted small"><i class="fa fa-pencil"></i></span><?php endif; ?><br>

                <?php if ($canEditTask): ?>
                    <?= Html::beginForm(
                        $contentContainer->createUrl('/todo/task/quick-update', ['id' => $task->id]),
                        'post',
                        ['class' => 'mt-1']
                    ) ?>
                    <?= Html::hiddenInput('field', 'due_date') ?>
                    <div class="d-flex align-items-center gap-2">
                        <?= Html::input(
                            'date',
                            'value',
                            $task->due_date,
                            [
                                'class' => 'form-control input-sm',
                                'style' => 'max-width:190px;height:34px;',
                                'onchange' => 'this.form.submit();',
                                'aria-label' => Yii::t('TodoModule.base', 'Fälligkeitsdatum ändern'),
                            ]
                        ) ?>
                        <?php if ($task->sync_to_calendar): ?>
                            <span class="small text-muted" title="Mit Kalender synchronisiert">
                                <i class="fa fa-calendar"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?= Html::endForm() ?>
                <?php else: ?>
                    <?= $task->due_date
                        ? Yii::$app->formatter->asDate($task->due_date) . ($task->sync_to_calendar ? ' · Kalender' : '')
                        : '—' ?>
                <?php endif; ?>
            </div>

        </div>


        <!-- ZUSTÄNDIG -->
        <?php if (!empty($task->users)): ?>

            <div class="mt-4">

                <strong><?= Yii::t('TodoModule.base', 'Zuständig') ?></strong>

                <div class="mt-2">

                    <?php foreach ($task->users as $user): ?>

                        <div class="d-flex align-items-center mb-1">

                            <?= UserImage::widget([
                                'user' => $user,
                                'width' => 24
                            ]) ?>

                            <span class="ms-2">
                                <?= Html::encode($user->displayName) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php endif; ?>

        <details class="panel panel-default mt-4" id="todo-notifications">
            <summary class="panel-heading" style="cursor:pointer;">
                <i class="fa fa-bell-o"></i>
                <strong><?= Yii::t('TodoModule.base', 'Meine Benachrichtigungen') ?></strong>
            </summary>
            <div class="panel-body">
                <?= Html::beginForm($contentContainer->createUrl('/todo/task/notification-preference', ['id' => $task->id]), 'post') ?>
                <?= Html::dropDownList('mode', $notificationMode, [
                    TaskNotificationPreferenceService::INHERIT => Yii::t('TodoModule.base', 'Persönlichen Standard verwenden: {setting}', [
                        '{setting}' => Yii::t('TodoModule.base', [
                            TaskNotificationPreferenceService::ALL => 'Alle ToDo-Benachrichtigungen',
                            TaskNotificationPreferenceService::IMPORTANT => 'Nur Kommentare und Zuweisungen',
                            TaskNotificationPreferenceService::REMINDERS => 'Nur Erinnerungen',
                            TaskNotificationPreferenceService::MUTED => 'Stumm',
                        ][$notificationDefault]),
                    ]),
                    TaskNotificationPreferenceService::ALL => Yii::t('TodoModule.base', 'Alle ToDo-Benachrichtigungen'),
                    TaskNotificationPreferenceService::IMPORTANT => Yii::t('TodoModule.base', 'Nur Kommentare und Zuweisungen'),
                    TaskNotificationPreferenceService::REMINDERS => Yii::t('TodoModule.base', 'Nur Erinnerungen'),
                    TaskNotificationPreferenceService::MUTED => Yii::t('TodoModule.base', 'Stumm'),
                ], ['class' => 'form-control']) ?>
                <p class="help-block mb-2">
                    <?= Yii::t('TodoModule.base', 'Diese Auswahl gilt nur für dich und nur für diese Aufgabe. Erwähnungen und HumHub-Follower bleiben unverändert.') ?>
                </p>
                <?= Html::submitButton(Yii::t('TodoModule.base', 'Speichern'), ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::endForm() ?>
            </div>
        </details>


        <!-- ERSTELLT -->
        <?php if ($task->content): ?>

            <?php $creator = User::findOne($task->content->created_by); ?>

            <?php if ($creator): ?>

                <div class="mt-4">

                    <strong><?= Yii::t('TodoModule.base', 'Erstellt') ?></strong>

                    <div class="mt-1 d-flex align-items-center">

                        <?= UserImage::widget([
                            'user' => $creator,
                            'width' => 24
                        ]) ?>

                        <span class="ms-2">

                            <?= Html::encode($creator->displayName) ?>

                            ·

                            <?= Yii::$app->formatter->asDatetime(
                                $task->content->created_at
                            ) ?>

                        </span>

                    </div>

                </div>

            <?php endif; ?>

        <?php endif; ?>


        <!-- LETZTE ÄNDERUNG -->
        <?php if ($task->content && $task->content->updated_at): ?>

            <div class="mt-2 text-muted">

                zuletzt geändert:

                <?= Yii::$app->formatter->asDatetime(
                    $task->content->updated_at
                ) ?>

            </div>

        <?php endif; ?>


        <!-- DATEIEN / FOTOS -->
        <?php $files = $task->files; ?>
        <?php if (!empty($files)): ?>

            <div class="mt-4">
                <strong><?= Yii::t('TodoModule.base', 'Dateien & Fotos') ?></strong>

                <div class="mt-2 d-flex flex-column gap-2">
                    <?php foreach ($files as $file): ?>
                        <?php
                        $isImage = str_starts_with((string) $file->mime_type, 'image/');
                        $displayTitle = trim((string) $file->title);
                        if ($displayTitle === '') {
                            $displayTitle = pathinfo($file->file_name, PATHINFO_FILENAME);
                        }
                        ?>

                        <div class="border rounded p-2 d-flex gap-3 align-items-start">
                            <?php if ($isImage): ?>
                                <?= Html::a(
                                    Html::img(
                                        $file->getUrl([], false),
                                        [
                                            'alt' => $displayTitle,
                                            'loading' => 'lazy',
                                            'style' => 'width:110px;height:80px;object-fit:cover;border-radius:4px;display:block;',
                                        ]
                                    ),
                                    $file->getUrl([], false),
                                    [
                                        'target' => '_blank',
                                        'rel' => 'noopener',
                                        'class' => 'flex-shrink-0',
                                        'title' => Yii::t('TodoModule.base', 'Foto öffnen'),
                                    ]
                                ) ?>
                            <?php else: ?>
                                <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-light rounded"
                                     style="width:70px;height:70px;font-size:28px;">
                                    <i class="fa fa-file-o text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-break">
                                    <?= Html::encode($displayTitle) ?>
                                </div>

                                <div class="small text-muted text-break mb-2">
                                    <?= Html::encode($file->file_name) ?>
                                </div>

                                <?php if ($canEditTask): ?>
                                    <?= Html::beginForm(
                                        $contentContainer->createUrl('/todo/task/update-file-title', [
                                            'id' => $task->id,
                                            'guid' => $file->guid,
                                        ]),
                                        'post',
                                        ['class' => 'd-flex gap-1 mb-2']
                                    ) ?>
                                    <?= Html::textInput('title', $displayTitle, [
                                        'class' => 'form-control form-control-sm',
                                        'maxlength' => 255,
                                        'placeholder' => Yii::t('TodoModule.base', 'Titel / kurze Beschreibung'),
                                        'aria-label' => Yii::t('TodoModule.base', 'Titel oder Beschreibung der Datei'),
                                    ]) ?>
                                    <?= Html::submitButton(
                                        '<i class="fa fa-save"></i>',
                                        [
                                            'class' => 'btn btn-sm btn-outline-secondary',
                                            'title' => Yii::t('TodoModule.base', 'Titel speichern'),
                                            'aria-label' => Yii::t('TodoModule.base', 'Titel speichern'),
                                        ]
                                    ) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>

                                <div class="d-flex gap-1 flex-wrap">
                                    <?= Html::a(
                                        $isImage ? 'Öffnen' : 'Download',
                                        $file->getUrl($isImage ? [] : ['download' => 1], false),
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'target' => $isImage ? '_blank' : null,
                                            'rel' => $isImage ? 'noopener' : null,
                                        ]
                                    ) ?>

                                    <?php if ($canEditTask): ?>
                                        <?= Html::a(
                                            'Löschen',
                                            $contentContainer->createUrl('/todo/task/delete-file', [
                                                'id' => $task->id,
                                                'guid' => $file->guid,
                                            ]),
                                            [
                                                'class' => 'btn btn-sm btn-outline-danger',
                                                'data-confirm' => Yii::t('TodoModule.base', 'Datei wirklich löschen?'),
                                                'data-method' => 'post',
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>


        <!-- DATEI DIREKT HINZUFÜGEN -->
        <?php if ($canEditTask): ?>
            <div class="mt-4">
                <?= Html::button(
                    '<i class="fa fa-paperclip"></i> Datei hinzufügen',
                    [
                        'class' => 'btn btn-sm btn-outline-secondary',
                        'type' => 'button',
                        'id' => 'todo-file-upload-open',
                    ]
                ) ?>
            </div>

            <div id="todo-file-upload-backdrop" class="todo-file-upload-backdrop" hidden>
                <div class="todo-file-upload-dialog" role="dialog" aria-modal="true" aria-labelledby="todo-file-upload-title">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong id="todo-file-upload-title"><?= Yii::t('TodoModule.base', 'Datei hinzufügen') ?></strong>
                        <?= Html::button('&times;', [
                            'class' => 'btn btn-sm btn-light',
                            'type' => 'button',
                            'id' => 'todo-file-upload-close',
                            'aria-label' => Yii::t('TodoModule.base', 'Dialog schliessen'),
                        ]) ?>
                    </div>

                    <?= Html::beginForm(
                        $contentContainer->createUrl('/todo/task/upload-file', ['id' => $task->id]),
                        'post',
                        ['enctype' => 'multipart/form-data', 'id' => 'todo-file-upload-form']
                    ) ?>

                    <div class="mb-3">
                        <?= Html::label('Datei', 'todo-upload-file', ['class' => 'form-label fw-semibold']) ?>
                        <?= Html::fileInput('uploadFile', null, [
                            'id' => 'todo-upload-file',
                            'class' => 'form-control',
                            'accept' => '.png,.jpg,.jpeg,.pdf,.doc,.docx,.xlsx',
                            'data-max-file-size' => $uploadMaxFileSize,
                            'data-error-file' => Yii::t('TodoModule.base', 'Die Datei «{name}» ist größer als das erlaubte Limit von {size}.'),
                            'aria-describedby' => 'todo-upload-file-help todo-upload-file-error',
                            'required' => true,
                        ]) ?>
                        <div id="todo-upload-file-help" class="small text-muted mt-1">
                            <?= Yii::t('TodoModule.base', 'Maximale Dateigröße: {size}. Erlaubt: PNG, JPG, PDF, DOC, DOCX und XLSX.', [
                                'size' => Yii::$app->formatter->asShortSize($uploadMaxFileSize),
                            ]) ?>
                        </div>
                        <div id="todo-upload-file-name" class="small text-muted mt-1"></div>
                        <div id="todo-upload-file-error" class="small text-danger" aria-live="polite"></div>
                    </div>

                    <div class="mb-3">
                        <?= Html::label('Titel / kurze Beschreibung (optional)', 'todo-upload-title', ['class' => 'form-label fw-semibold']) ?>
                        <?= Html::textInput('title', '', [
                            'id' => 'todo-upload-title',
                            'class' => 'form-control',
                            'maxlength' => 255,
                            'placeholder' => Yii::t('TodoModule.base', 'z.B. Bühne vor dem Aufbau'),
                        ]) ?>
                        <div class="small text-muted mt-1"><?= Yii::t('TodoModule.base', 'Ohne Titel wird automatisch der Dateiname verwendet.') ?></div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <?= Html::button('Abbrechen', [
                            'class' => 'btn btn-sm btn-light',
                            'type' => 'button',
                            'id' => 'todo-file-upload-cancel',
                        ]) ?>
                        <?= Html::submitButton('<i class="fa fa-upload"></i> Hochladen', [
                            'class' => 'btn btn-sm btn-primary',
                        ]) ?>
                    </div>

                    <?= Html::endForm() ?>
                </div>
            </div>

            <style>
                .todo-file-upload-backdrop {
                    position: fixed;
                    inset: 0;
                    z-index: 1055;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    background: rgba(0, 0, 0, .35);
                }
                .todo-file-upload-backdrop[hidden] { display: none !important; }
                .todo-file-upload-dialog {
                    width: min(520px, 100%);
                    max-height: calc(100vh - 40px);
                    overflow: auto;
                    background: var(--hh-background-color-main, #fff);
                    color: var(--hh-text-color-main, inherit);
                    border-radius: 6px;
                    padding: 18px;
                    box-shadow: 0 10px 35px rgba(0, 0, 0, .25);
                }
            </style>

            <?php
            $this->registerJs(<<<'JS'
(function () {
    const backdrop = document.getElementById('todo-file-upload-backdrop');
    const openButton = document.getElementById('todo-file-upload-open');
    const closeButton = document.getElementById('todo-file-upload-close');
    const cancelButton = document.getElementById('todo-file-upload-cancel');
    const fileInput = document.getElementById('todo-upload-file');
    const fileName = document.getElementById('todo-upload-file-name');
    const fileError = document.getElementById('todo-upload-file-error');
    const titleInput = document.getElementById('todo-upload-title');

    if (!backdrop || !openButton) {
        return;
    }

    const openDialog = function () {
        backdrop.hidden = false;
        window.setTimeout(function () { fileInput && fileInput.focus(); }, 0);
    };

    const closeDialog = function () {
        backdrop.hidden = true;
        if (fileInput) fileInput.value = '';
        if (fileName) fileName.textContent = '';
        if (fileError) fileError.textContent = '';
        if (fileInput) fileInput.setCustomValidity('');
        if (titleInput) titleInput.value = '';
        openButton.focus();
    };

    openButton.addEventListener('click', openDialog);
    closeButton && closeButton.addEventListener('click', closeDialog);
    cancelButton && cancelButton.addEventListener('click', closeDialog);

    backdrop.addEventListener('click', function (event) {
        if (event.target === backdrop) {
            closeDialog();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !backdrop.hidden) {
            closeDialog();
        }
    });

    fileInput && fileInput.addEventListener('change', function () {
        const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        const maxFile = Number(fileInput.dataset.maxFileSize || 0);
        const message = file && maxFile > 0 && file.size > maxFile
            ? fileInput.dataset.errorFile.replace('{name}', file.name).replace('{size}', (maxFile / 1024 / 1024).toLocaleString(undefined, {maximumFractionDigits: 2}) + ' MB')
            : '';
        fileName.textContent = file ? file.name : '';
        fileInput.setCustomValidity(message);
        if (fileError) fileError.textContent = message;
    });
})();
JS
            );
            ?>
        <?php endif; ?>

        <!-- AKTIVITÄTSPROTOKOLL (standardmässig eingeklappt) -->
        <?php $historyEntries = $task->getHistoryEntries()->limit(100)->all(); ?>
        <details class="panel panel-default mt-4" id="todo-history">
            <summary class="panel-heading d-flex align-items-center justify-content-between"
                     style="cursor:pointer;list-style:none;">
                <span><i class="fa fa-history"></i> <strong><?= Yii::t('TodoModule.base', 'Verlauf') ?></strong></span>
                <span class="badge bg-secondary"><?= count($historyEntries) ?></span>
            </summary>
            <div class="panel-body p-0">
                <?php if ($historyEntries === []): ?>
                    <div class="text-muted small p-3"><?= Yii::t('TodoModule.base', 'Noch keine Aktivitäten protokolliert.') ?></div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($historyEntries as $history): ?>
                            <div class="list-group-item d-flex gap-2 align-items-start">
                                <div class="flex-shrink-0">
                                    <?php if ($history->user): ?>
                                        <?= UserImage::widget(['user' => $history->user, 'width' => 28]) ?>
                                    <?php else: ?>
                                        <span class="fa-stack" style="font-size:14px;">
                                            <i class="fa fa-circle fa-stack-2x text-muted"></i>
                                            <i class="fa fa-cog fa-stack-1x fa-inverse"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="text-break"><?= Html::encode($history->message) ?></div>
                                    <div class="small text-muted">
                                        <?= Html::encode($history->user ? $history->user->displayName : 'System') ?>
                                        · <?= Yii::$app->formatter->asDatetime($history->created_at) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </details>

        <!-- KOMMUNIKATION -->
        <div class="mt-4" id="todo-communication">
            <strong><?= Yii::t('TodoModule.base', 'Kommunikation') ?></strong>
            <div class="small text-muted mt-1">
                Nachrichten, Rückfragen und Absprachen zu dieser Aufgabe.
            </div>

            <div class="todo-communication-comments mt-2">
                <?php
                // HumHub 1.19 changed the comment widget from an ActiveRecord
                // object to its Content model. Keep the module compatible with
                // both the 1.18 and 1.19 APIs.
                $commentWidgetConfig = property_exists(Comments::class, 'content')
                    ? ['content' => $task->content]
                    : ['object' => $task];
                $commentWidgetConfig['viewMode'] = Comments::VIEW_MODE_FULL;
                ?>
                <?= Comments::widget($commentWidgetConfig) ?>
            </div>
        </div>

        <style>
            /* The standard HumHub comment widget is collapsed in wall-preview mode.
               On the dedicated ToDo detail page communication should always be visible. */
            #todo-communication .comment-container {
                display: block !important;
                margin-top: .5rem !important;
                border-radius: 6px;
            }
            #todo-communication .comment-container:empty {
                display: none !important;
            }
        </style>



    </div>

</div>
<?php
$checklistPointLabel = Json::htmlEncode(Yii::t('TodoModule.base', 'Checklistenpunkt'));
$checklistPointsLabel = Json::htmlEncode(Yii::t('TodoModule.base', 'Checklistenpunkte'));
$openChecklistConfirm = Json::htmlEncode(Yii::t('TodoModule.base', 'Diese Aufgabe enthält noch {count} offene {label}. Trotzdem schliessen?'));
$this->registerJs(<<<JS
window.todoConfirmClose = function (select, openChecklistCount) {
    if (select.value !== 'geschlossen' || openChecklistCount < 1) {
        select.form.submit();
        return true;
    }

    var label = openChecklistCount === 1 ? {$checklistPointLabel} : {$checklistPointsLabel};
    var message = {$openChecklistConfirm}
        .replace('{count}', openChecklistCount)
        .replace('{label}', label);

    if (window.confirm(message)) {
        var confirmField = select.form.querySelector('.js-confirm-open-checklist');
        if (confirmField) {
            confirmField.value = '1';
        }
        select.form.submit();
        return true;
    }

    // Auswahl wieder auf den bisherigen Status zurücksetzen.
    select.value = select.getAttribute('data-current-status') || 'offen';
    return false;
};
JS
);
?>
