<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use humhub\modules\user\widgets\Image as UserImage;
use yii\helpers\Url;

$groupBy = $groupBy ?? Yii::$app->request->get('group', 'list');
$isMy = Yii::$app->request->get('my');
$currentDone = Yii::$app->request->get('done');
$viewMode = $viewMode ?? 'list';
$showArchive = $showArchive ?? false;
$showTrash = $showTrash ?? false;
$filterParams = [
    'my' => $isMy,
    'priority' => Yii::$app->request->get('priority'),
    'list_id' => Yii::$app->request->get('list_id'),
    'assignee_id' => Yii::$app->request->get('assignee_id'),
    'label_id' => Yii::$app->request->get('label_id'),
];
$exportParams = Yii::$app->request->getQueryParams();
unset($exportParams['r'], $exportParams['cguid'], $exportParams['page'], $exportParams['per-page']);

$taskRow = function ($task, bool $showUsers = true) use ($contentContainer, $currentDone, $isMy, $groupBy, $showArchive, $showTrash) {
    $isOverdue = !empty($task->due_date) && $task->due_date < date('Y-m-d') && $task->status !== 'geschlossen';
    $priorityClass = match ($task->priority) {
        'hoch' => 'badge bg-danger',
        'mittel' => 'badge bg-warning text-dark',
        'niedrig' => 'badge bg-success',
        default => 'badge bg-secondary',
    };
    $statusClass = match ($task->status) {
        'in_bearbeitung' => 'badge bg-info text-dark',
        'geschlossen' => 'badge bg-secondary',
        default => 'badge bg-light text-dark border',
    };
    $statusLabel = match ($task->status) {
        'in_bearbeitung' => Yii::t('TodoModule.base', 'IN BEARBEITUNG'),
        'geschlossen' => Yii::t('TodoModule.base', 'GESCHLOSSEN'),
        default => Yii::t('TodoModule.base', 'OFFEN'),
    };
    ?>
    <?php $taskViewUrl = $showTrash ? null : $contentContainer->createUrl('/todo/task/view', ['id' => $task->id]); ?>
    <div class="d-flex align-items-center gap-2 px-2 py-2 border-top todo-list-row"
         <?= $taskViewUrl ? 'role="link" tabindex="0" data-task-url="' . Html::encode($taskViewUrl) . '" style="cursor:pointer;"' : '' ?>>
        <div class="flex-grow-1 min-width-0">
            <div class="d-flex flex-wrap align-items-center gap-1">
                <span class="fw-semibold text-break"><?= Html::encode($task->title) ?></span>
                <span class="<?= $priorityClass ?>"><?= ucfirst(Html::encode($task->priority)) ?></span>
                <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
                <?php if ($isOverdue): ?><span class="badge bg-danger"><?= Yii::t('TodoModule.base', 'ÜBERFÄLLIG') ?></span><?php endif; ?>
                <?php foreach ($task->taskLabels as $label): ?>
                    <span class="badge" style="background:<?= Html::encode($label->color) ?>;color:#fff;"><?= Html::encode($label->name) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="small text-muted d-flex flex-wrap align-items-center gap-2 mt-1">
                <?php if ($task->due_date): ?>
                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>"><i class="fa fa-calendar"></i> <?= Yii::$app->formatter->asDate($task->due_date) ?></span>
                <?php endif; ?>
                <?php if ($showUsers && !empty($task->users)): ?>
                    <span>
                        <?php foreach ($task->users as $user): ?>
                            <?= UserImage::widget(['user' => $user, 'width' => 18]) ?>
                            <span class="me-1"><?= Html::encode($user->displayName) ?></span>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($task->canManage() || $task->canDelete() || ($showTrash && $contentContainer->isAdmin())): ?>
        <div class="d-flex gap-1 flex-shrink-0" data-task-actions>
            <?php if ($showTrash): ?>
                <?php if ($task->canDelete()): ?>
                    <?= Html::beginForm($contentContainer->createUrl('/todo/task/restore-trash', ['id' => $task->id]), 'post', ['class' => 'd-inline']) ?>
                    <?= Html::submitButton('<i class="fa fa-undo"></i>', [
                        'class' => 'btn btn-xs btn-outline-success',
                        'title' => Yii::t('TodoModule.base', 'Wiederherstellen'),
                        'aria-label' => Yii::t('TodoModule.base', 'Wiederherstellen'),
                        'onclick' => 'event.stopPropagation();',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
                <?php if ($contentContainer->isAdmin()): ?>
                    <?= Html::beginForm($contentContainer->createUrl('/todo/task/permanent-delete', ['id' => $task->id]), 'post', ['class' => 'd-inline']) ?>
                    <?= Html::submitButton('<i class="fa fa-trash"></i>', [
                        'class' => 'btn btn-xs btn-danger',
                        'title' => Yii::t('TodoModule.base', 'Endgültig löschen'),
                        'aria-label' => Yii::t('TodoModule.base', 'Endgültig löschen'),
                        'data-confirm' => Yii::t('TodoModule.base', 'Aufgabe endgültig löschen? Dies kann nicht rückgängig gemacht werden.'),
                        'onclick' => 'event.stopPropagation();',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            <?php else: ?>
            <?php if ($task->canManage()): ?>
            <?= Html::a(
                '<i class="fa fa-pencil"></i>',
                $contentContainer->createUrl('/todo/task/update', ['id' => $task->id]),
                [
                    'class' => 'btn btn-xs btn-outline-primary',
                    'title' => Yii::t('TodoModule.base', 'Bearbeiten'),
                    'aria-label' => Yii::t('TodoModule.base', 'Bearbeiten'),
                    'onclick' => 'event.stopPropagation();',
                    'onkeydown' => 'event.stopPropagation();',
                ]
            ) ?>
            <?php if ($showArchive): ?>
                <?= Html::beginForm($contentContainer->createUrl('/todo/task/restore', ['id' => $task->id]), 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('<i class="fa fa-undo"></i>', [
                    'class' => 'btn btn-xs btn-outline-success',
                    'title' => Yii::t('TodoModule.base', 'Wiederherstellen'),
                    'aria-label' => Yii::t('TodoModule.base', 'Wiederherstellen'),
                    'onclick' => 'event.stopPropagation();',
                    'onkeydown' => 'event.stopPropagation();',
                ]) ?>
                <?= Html::endForm() ?>
            <?php elseif ($task->status === 'geschlossen'): ?>
                <?= Html::beginForm($contentContainer->createUrl('/todo/task/archive', ['id' => $task->id]), 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('<i class="fa fa-archive"></i>', [
                    'class' => 'btn btn-xs btn-outline-secondary',
                    'title' => Yii::t('TodoModule.base', 'Archivieren'),
                    'aria-label' => Yii::t('TodoModule.base', 'Archivieren'),
                    'data-confirm' => Yii::t('TodoModule.base', 'Aufgabe archivieren?'),
                    'onclick' => 'event.stopPropagation();',
                    'onkeydown' => 'event.stopPropagation();',
                ]) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?php endif; ?>
            <?php if ($task->canDelete()): ?>
            <?= Html::a(
                '<i class="fa fa-trash"></i>',
                $contentContainer->createUrl('/todo/task/delete', [
                    'id' => $task->id,
                    'done' => $currentDone,
                    'my' => $isMy,
                    'group' => $groupBy,
                ]),
                [
                    'class' => 'btn btn-xs btn-outline-danger',
                    'title' => Yii::t('TodoModule.base', 'Löschen'),
                    'aria-label' => Yii::t('TodoModule.base', 'Löschen'),
                    'data-confirm' => Yii::t('TodoModule.base', 'Wirklich löschen?'),
                    'data-method' => 'post',
                    'onclick' => 'event.stopPropagation();',
                    'onkeydown' => 'event.stopPropagation();',
                ]
            ) ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
};

$kanbanCard = function ($task) use ($contentContainer) {
    $isOverdue = !empty($task->due_date) && $task->due_date < date('Y-m-d') && $task->status !== 'geschlossen';
    $priorityClass = match ($task->priority) {
        'hoch' => 'bg-danger',
        'mittel' => 'bg-warning text-dark',
        'niedrig' => 'bg-success',
        default => 'bg-secondary',
    };
    $taskViewUrl = $contentContainer->createUrl('/todo/task/view', ['id' => $task->id]);
    $statusUrl = $contentContainer->createUrl('/todo/task/kanban-status', ['id' => $task->id]);
    $canMove = $task->canWorkOn();
    ?>
    <article class="todo-kanban-card"
             data-task-id="<?= (int) $task->id ?>"
             data-task-url="<?= Html::encode($taskViewUrl) ?>"
             data-current-status="<?= Html::encode($task->status) ?>"
             data-status-url="<?= Html::encode($statusUrl) ?>"
             role="link"
             tabindex="0"
             draggable="true">
        <a href="<?= Html::encode($taskViewUrl) ?>" class="todo-kanban-title"><?= Html::encode($task->title) ?></a>
        <div class="d-flex flex-wrap gap-1 mt-2">
            <span class="badge <?= $priorityClass ?>"><?= Html::encode(Yii::t('TodoModule.base', ucfirst($task->priority))) ?></span>
            <?php if ($isOverdue): ?><span class="badge bg-danger"><?= Yii::t('TodoModule.base', 'ÜBERFÄLLIG') ?></span><?php endif; ?>
            <?php if ($task->getOpenBlockingTasks()->exists()): ?><span class="badge bg-warning text-dark"><?= Yii::t('TodoModule.base', 'BLOCKIERT') ?></span><?php endif; ?>
            <?php foreach ($task->taskLabels as $label): ?>
                <span class="badge" style="background:<?= Html::encode($label->color) ?>;color:#fff;"><?= Html::encode($label->name) ?></span>
            <?php endforeach; ?>
        </div>
        <?php if ($task->due_date || !empty($task->users)): ?>
        <div class="todo-kanban-meta">
            <?php if ($task->due_date): ?>
                <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>"><i class="fa fa-calendar"></i> <?= Yii::$app->formatter->asDate($task->due_date) ?></span>
            <?php endif; ?>
            <?php if (!empty($task->users)): ?>
                <span class="todo-kanban-users">
                    <?php foreach ($task->users as $user): ?>
                        <span title="<?= Html::encode($user->displayName) ?>"><?= UserImage::widget(['user' => $user, 'width' => 22]) ?></span>
                    <?php endforeach; ?>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($canMove): ?>
            <label class="todo-kanban-mobile-status">
                <span class="sr-only"><?= Yii::t('TodoModule.base', 'Status') ?></span>
                <select class="form-control form-control-sm" data-kanban-status-select>
                    <option value="offen" <?= $task->status === 'offen' ? 'selected' : '' ?>><?= Yii::t('TodoModule.base', 'Offen') ?></option>
                    <option value="in_bearbeitung" <?= $task->status === 'in_bearbeitung' ? 'selected' : '' ?>><?= Yii::t('TodoModule.base', 'In Bearbeitung') ?></option>
                    <option value="geschlossen" <?= $task->status === 'geschlossen' ? 'selected' : '' ?>><?= Yii::t('TodoModule.base', 'Geschlossen') ?></option>
                </select>
            </label>
        <?php endif; ?>
    </article>
    <?php
};
?>

<div class="panel panel-default">
    <div class="panel-heading d-flex flex-wrap justify-content-between align-items-center gap-2 todo-task-heading">
        <div class="d-flex align-items-center gap-2 flex-wrap todo-task-heading-main">
            <strong style="white-space:nowrap;"><?= Yii::t('TodoModule.base', 'ToDo Liste') ?></strong>
            <form method="get" action="<?= $contentContainer->createUrl('/todo/search/index') ?>" class="d-flex gap-1 align-items-center todo-task-search">
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="<?= Yii::t('TodoModule.base', 'Suche...') ?>">
                <button class="btn btn-sm btn-outline-primary"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="d-flex gap-1 todo-task-toolbar">
            <div class="btn-group" role="group" aria-label="<?= Yii::t('TodoModule.base', 'Ansicht') ?>">
                <?= Html::a('<i class="fa fa-list"></i> ' . Yii::t('TodoModule.base', 'Liste'), $contentContainer->createUrl('/todo/task/index', array_filter($filterParams + ['view' => 'list'])), ['class' => 'btn btn-sm ' . ($viewMode === 'list' ? 'btn-primary' : 'btn-default')]) ?>
                <?= Html::a('<i class="fa fa-columns"></i> ' . Yii::t('TodoModule.base', 'Kanban'), $contentContainer->createUrl('/todo/task/index', array_filter($filterParams + ['view' => 'kanban'])), ['class' => 'btn btn-sm ' . ($viewMode === 'kanban' ? 'btn-primary' : 'btn-default')]) ?>
            </div>
            <?php if ($contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\EditTasks())): ?>
                <?= Html::a(Yii::t('TodoModule.base', 'Aufgabenlisten verwalten'), $contentContainer->createUrl('/todo/task-list/index'), ['class' => 'btn btn-default btn-sm']) ?>
                <?= Html::a(Yii::t('TodoModule.base', 'Labels'), $contentContainer->createUrl('/todo/label/index'), ['class' => 'btn btn-default btn-sm']) ?>
            <?php endif; ?>
            <?= Html::a(Yii::t('TodoModule.base', 'Vorlagen'), $contentContainer->createUrl('/todo/template/index'), ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-download"></i> ' . Yii::t('TodoModule.base', 'CSV-Export'), $contentContainer->createUrl('/todo/task/export', $exportParams), [
                'class' => 'btn btn-default btn-sm',
                'title' => Yii::t('TodoModule.base', 'Aktuell gefilterte Aufgaben als CSV exportieren'),
            ]) ?>
            <?php if ($contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\CreateTasks())): ?>
                <?= Html::a(Yii::t('TodoModule.base', 'Neue Aufgabe'), $contentContainer->createUrl('/todo/task/create'), ['class' => 'btn btn-success btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <details class="todo-kanban-filters mb-3">
            <summary class="btn btn-sm btn-default"><i class="fa fa-filter"></i> <?= Yii::t('TodoModule.base', 'Filter') ?></summary>
            <form method="get" class="row g-2 mt-2 align-items-end">
                <input type="hidden" name="view" value="<?= Html::encode($viewMode) ?>">
                <?php if ($viewMode === 'list'): ?><input type="hidden" name="group" value="<?= Html::encode($groupBy) ?>"><?php endif; ?>
                <?php if ($isMy): ?><input type="hidden" name="my" value="1"><?php endif; ?>
                <div class="col-sm-3">
                    <label class="control-label"><?= Yii::t('TodoModule.base', 'Priorität') ?></label>
                    <?= Html::dropDownList('priority', Yii::$app->request->get('priority'), ['' => Yii::t('TodoModule.base', 'Alle'), 'hoch' => Yii::t('TodoModule.base', 'Hoch'), 'mittel' => Yii::t('TodoModule.base', 'Mittel'), 'niedrig' => Yii::t('TodoModule.base', 'Niedrig')], ['class' => 'form-control']) ?>
                </div>
                <div class="col-sm-3">
                    <label class="control-label"><?= Yii::t('TodoModule.base', 'Aufgabenliste') ?></label>
                    <?php $listOptions = []; foreach ($taskLists as $list) $listOptions[$list->id] = $list->name; ?>
                    <?= Html::dropDownList('list_id', Yii::$app->request->get('list_id'), ['' => Yii::t('TodoModule.base', 'Alle')] + $listOptions, ['class' => 'form-control']) ?>
                </div>
                <div class="col-sm-3">
                    <label class="control-label"><?= Yii::t('TodoModule.base', 'Zuständig') ?></label>
                    <?php $userOptions = []; foreach ($spaceUsers as $user) $userOptions[$user->id] = $user->displayName; ?>
                    <?= Html::dropDownList('assignee_id', Yii::$app->request->get('assignee_id'), ['' => Yii::t('TodoModule.base', 'Alle')] + $userOptions, ['class' => 'form-control']) ?>
                </div>
                <div class="col-sm-3">
                    <label class="control-label"><?= Yii::t('TodoModule.base', 'Label') ?></label>
                    <?php $labelOptions = []; foreach ($taskLabels as $label) $labelOptions[$label->id] = $label->name; ?>
                    <?= Html::dropDownList('label_id', Yii::$app->request->get('label_id'), ['' => Yii::t('TodoModule.base', 'Alle')] + $labelOptions, ['class' => 'form-control']) ?>
                </div>
                <div class="col-12 d-flex gap-1">
                    <button class="btn btn-primary btn-sm"><?= Yii::t('TodoModule.base', 'Filtern') ?></button>
                    <?= Html::a(Yii::t('TodoModule.base', 'Zurücksetzen'), $contentContainer->createUrl('/todo/task/index', ['view' => $viewMode, 'group' => $groupBy, 'my' => $isMy]), ['class' => 'btn btn-default btn-sm']) ?>
                </div>
            </form>
        </details>
        <div class="mb-2 d-flex flex-wrap gap-1">
            <?= Html::a(Yii::t('TodoModule.base', 'Alle Tasks'), $contentContainer->createUrl('/todo/task/index', ['group' => $groupBy, 'view' => $viewMode]), ['class' => 'btn btn-sm ' . (!$isMy && !$currentDone && !$showArchive && !$showTrash ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a(Yii::t('TodoModule.base', 'Meine Tasks'), $contentContainer->createUrl('/todo/task/index', ['my' => 1, 'done' => $currentDone, 'group' => $groupBy, 'view' => $viewMode]), ['class' => 'btn btn-sm ' . ($isMy ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?php if ($viewMode === 'list'): ?><?= Html::a(Yii::t('TodoModule.base', 'Erledigt'), $contentContainer->createUrl('/todo/task/index', ['done' => 1, 'my' => $isMy, 'group' => $groupBy, 'view' => $viewMode]), ['class' => 'btn btn-sm ' . ($currentDone ? 'btn-primary' : 'btn-outline-secondary')]) ?><?php endif; ?>
            <?= Html::a('<i class="fa fa-archive"></i> ' . Yii::t('TodoModule.base', 'Archiv'), $contentContainer->createUrl('/todo/task/index', ['archive' => 1, 'group' => 'date']), ['class' => 'btn btn-sm ' . ($showArchive ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('TodoModule.base', 'Papierkorb'), $contentContainer->createUrl('/todo/task/index', ['trash' => 1, 'group' => 'date']), ['class' => 'btn btn-sm ' . ($showTrash ? 'btn-primary' : 'btn-outline-secondary')]) ?>
        </div>
        <?php if ($showTrash): ?>
            <div class="alert alert-info py-2">
                <?= Yii::t('TodoModule.base', 'Aufgaben im Papierkorb werden nach 30 Tagen automatisch endgültig gelöscht.') ?>
            </div>
        <?php endif; ?>
        <?php if ($viewMode === 'list'): ?>
        <div class="mb-3 d-flex flex-wrap gap-1">
            <?= Html::a(Yii::t('TodoModule.base', 'Nach Aufgabenliste'), $contentContainer->createUrl('/todo/task/index', ['group' => 'list', 'my' => $isMy, 'done' => $currentDone, 'archive' => $showArchive ?: null, 'trash' => $showTrash ?: null]), ['class' => 'btn btn-sm ' . ($groupBy === 'list' ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a(Yii::t('TodoModule.base', 'Nach Datum'), $contentContainer->createUrl('/todo/task/index', ['group' => 'date', 'my' => $isMy, 'done' => $currentDone, 'archive' => $showArchive ?: null, 'trash' => $showTrash ?: null]), ['class' => 'btn btn-sm ' . ($groupBy === 'date' ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a(Yii::t('TodoModule.base', 'Nach Zuständig'), $contentContainer->createUrl('/todo/task/index', ['group' => 'user', 'my' => $isMy, 'done' => $currentDone, 'archive' => $showArchive ?: null, 'trash' => $showTrash ?: null]), ['class' => 'btn btn-sm ' . ($groupBy === 'user' ? 'btn-primary' : 'btn-outline-secondary')]) ?>
        </div>
        <?php endif; ?>

        <?php if ($viewMode === 'kanban'): ?>
            <div class="todo-kanban-board" data-order-url="<?= Html::encode($contentContainer->createUrl('/todo/task/kanban-order')) ?>">
                <?php foreach (['offen' => Yii::t('TodoModule.base', 'Offen'), 'in_bearbeitung' => Yii::t('TodoModule.base', 'In Bearbeitung'), 'geschlossen' => Yii::t('TodoModule.base', 'Geschlossen')] as $status => $label): ?>
                    <?php $columnTasks = array_values(array_filter($tasks, static fn($task) => $task->status === $status)); ?>
                    <section class="todo-kanban-column" data-kanban-status="<?= $status ?>">
                        <header><strong><?= $label ?></strong><span class="badge bg-secondary" data-kanban-count><?= count($columnTasks) ?></span></header>
                        <div class="todo-kanban-dropzone">
                            <?php foreach ($columnTasks as $task) $kanbanCard($task); ?>
                            <div class="todo-kanban-empty<?= $columnTasks ? ' d-none' : '' ?>"><?= Yii::t('TodoModule.base', 'Keine Aufgaben vorhanden.') ?></div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php elseif ($groupBy === 'list'): ?>
            <?php foreach ($tasksByList as $group): ?>
                <?php
                $list = $group['list'];
                $groupTasks = $group['tasks'];
                if (empty($groupTasks) && !$list) continue;
                $name = $list ? $list->name : Yii::t('TodoModule.base', 'Unsortiert');
                $color = $list ? $list->color : '#6c757d';
                ?>
                <details class="panel panel-default mb-2" open>
                    <summary class="panel-heading d-flex align-items-center justify-content-between" style="cursor:pointer; list-style:none; border-left:4px solid <?= Html::encode($color) ?>;">
                        <span><strong><?= Html::encode($name) ?></strong> <span class="text-muted">(<?= count($groupTasks) ?>)</span></span>
                        <span class="d-flex align-items-center gap-1">
                            <?php if ($contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\CreateTasks())): ?>
                            <?= Html::a(
                                '<i class="fa fa-plus"></i>',
                                $list
                                    ? $contentContainer->createUrl('/todo/task/create', ['list_id' => $list->id])
                                    : $contentContainer->createUrl('/todo/task/create'),
                                [
                                    'class' => 'btn btn-xs btn-success',
                                    'title' => $list ? 'Aufgabe in dieser Liste erstellen' : 'Unsortierte Aufgabe erstellen',
                                    'onclick' => 'event.stopPropagation();',
                                ]
                            ) ?>
                            <?php endif; ?>
                            <i class="fa fa-chevron-down text-muted"></i>
                        </span>
                    </summary>
                    <div class="panel-body p-0">
                        <?php if (empty($groupTasks)): ?>
                            <div class="text-muted small p-2"><?= $currentDone ? Yii::t('TodoModule.base', 'Noch keine erledigten Aufgaben in dieser Liste.') : Yii::t('TodoModule.base', 'Noch keine Aufgaben in dieser Liste.') ?></div>
                        <?php else: ?>
                            <?php foreach ($groupTasks as $task) $taskRow($task, true); ?>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endforeach; ?>

        <?php elseif ($groupBy === 'user'): ?>
            <?php foreach ($tasksByUser as $userName => $userTasks): ?>
                <details class="panel panel-default mb-2" open>
                    <summary class="panel-heading" style="cursor:pointer; list-style:none;"><strong><?= Html::encode($userName) ?></strong> <span class="text-muted">(<?= count($userTasks) ?>)</span></summary>
                    <div class="panel-body p-0"><?php foreach ($userTasks as $task) $taskRow($task, false); ?></div>
                </details>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="border rounded">
                <?php if (empty($tasks)): ?><div class="text-muted p-3"><?= Yii::t('TodoModule.base', 'Keine Aufgaben vorhanden.') ?></div><?php endif; ?>
                <?php foreach ($tasks as $task) $taskRow($task, true); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= LinkPager::widget(['pagination' => $pagination]) ?>

<?php
$this->registerCss(<<<CSS
.todo-list-row:hover,
.todo-list-row:focus {
    background: rgba(0, 0, 0, 0.035);
    outline: none;
}
.todo-list-row:focus {
    box-shadow: inset 0 0 0 2px rgba(0, 123, 255, 0.18);
}
.todo-task-heading,
.todo-task-heading-main,
.todo-task-toolbar { min-width:0; }
.todo-task-search { width:200px; max-width:100%; }
.todo-task-search input { width:100%; min-width:0; }
.todo-kanban-board { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; align-items:start; }
.todo-kanban-column { background:#f3f5f7; border:1px solid #dfe3e7; border-radius:6px; min-width:0; }
.todo-kanban-column > header { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; }
.todo-kanban-dropzone { min-height:90px; padding:0 8px 8px; }
.todo-kanban-column.is-drag-over { box-shadow:inset 0 0 0 2px #21a1b3; }
.todo-kanban-card { background:#fff; border:1px solid #dfe3e7; border-radius:5px; padding:10px; margin-bottom:8px; box-shadow:0 1px 2px rgba(0,0,0,.05); }
.todo-kanban-card[draggable="true"] { cursor:grab; }
.todo-kanban-card.is-moving { opacity:.55; }
.todo-kanban-title { color:inherit; font-weight:600; overflow-wrap:anywhere; }
.todo-kanban-meta { display:flex; justify-content:space-between; gap:8px; align-items:center; color:#6c757d; font-size:12px; margin-top:8px; }
.todo-kanban-users { display:flex; }
.todo-kanban-users > span + span { margin-left:-5px; }
.todo-kanban-empty { color:#777; font-size:12px; text-align:center; padding:16px 6px; }
.todo-kanban-mobile-status { display:none; margin-top:8px; }
@media (max-width: 767px) {
    .todo-task-heading { display:block !important; }
    .todo-task-heading-main { width:100%; }
    .todo-task-search { flex:1 1 180px; width:auto; }
    .todo-task-toolbar {
        display:grid !important;
        grid-template-columns:repeat(2,minmax(0,1fr));
        width:100%;
        margin-top:10px;
    }
    .todo-task-toolbar > .btn,
    .todo-task-toolbar > .btn-group { width:100%; min-width:0; }
    .todo-task-toolbar > .btn {
        white-space:normal;
        overflow-wrap:anywhere;
    }
    .todo-task-toolbar > .btn-group { display:flex; }
    .todo-task-toolbar > .btn-group > .btn { flex:1 1 50%; min-width:0; }
    .todo-kanban-board { grid-template-columns:1fr; }
    .todo-kanban-card[draggable="true"] { cursor:default; }
    .todo-kanban-mobile-status { display:block; }
    .todo-list-row { align-items:flex-start !important; }
    .todo-list-row [data-task-actions] { flex-wrap:wrap; justify-content:flex-end; }
}
CSS
);

$this->registerJs(<<<'JS'
document.querySelectorAll('.todo-list-row[data-task-url]').forEach(function (row) {
    if (row.dataset.clickableInitialized === '1') {
        return;
    }
    row.dataset.clickableInitialized = '1';

    function openTask(event) {
        if (event.target.closest('[data-task-actions], a, button, input, select, textarea, label, form')) {
            return;
        }

        window.location.href = row.dataset.taskUrl;
    }

    row.addEventListener('click', openTask);

    row.addEventListener('keydown', function (event) {
        if ((event.key === 'Enter' || event.key === ' ') &&
            !event.target.closest('[data-task-actions], a, button, input, select, textarea, label, form')) {
            event.preventDefault();
            window.location.href = row.dataset.taskUrl;
        }
    });
});

(function () {
    var board = document.querySelector('.todo-kanban-board');
    if (!board) return;
    var csrfParam = document.querySelector('meta[name="csrf-param"]')?.content;
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    var draggedCard = null;
    var sourceZone = null;

    function refreshColumns() {
        board.querySelectorAll('.todo-kanban-column').forEach(function (column) {
            var count = column.querySelectorAll('.todo-kanban-card').length;
            column.querySelector('[data-kanban-count]').textContent = count;
            column.querySelector('.todo-kanban-empty').classList.toggle('d-none', count > 0);
        });
    }

    async function saveOrder(zone) {
        if (!zone) return;
        var ids = Array.from(zone.querySelectorAll('.todo-kanban-card')).map(function (card) { return Number(card.dataset.taskId); });
        var body = new FormData();
        body.append('task_ids', JSON.stringify(ids));
        if (csrfParam && csrfToken) body.append(csrfParam, csrfToken);
        var response = await fetch(board.dataset.orderUrl, {method:'POST', body:body, headers:{'X-Requested-With':'XMLHttpRequest'}});
        if (!response.ok) throw new Error('Die persönliche Reihenfolge konnte nicht gespeichert werden.');
    }

    function cardAfterPointer(zone, clientY) {
        return Array.from(zone.querySelectorAll('.todo-kanban-card:not(.is-moving)')).find(function (card) {
            var rect = card.getBoundingClientRect();
            return clientY < rect.top + rect.height / 2;
        }) || null;
    }

    async function changeStatus(card, newStatus, confirmed) {
        var oldStatus = card.dataset.currentStatus;
        if (oldStatus === newStatus) return true;
        var body = new FormData();
        body.append('status', newStatus);
        if (csrfParam && csrfToken) body.append(csrfParam, csrfToken);
        if (confirmed) body.append('confirm_open_checklist', '1');
        card.classList.add('is-moving');
        try {
            var response = await fetch(card.dataset.statusUrl, {method:'POST', body:body, headers:{'X-Requested-With':'XMLHttpRequest'}});
            var result = await response.json();
            if (result.requiresConfirmation && window.confirm(result.message)) {
                return changeStatus(card, newStatus, true);
            }
            if (!response.ok || !result.success) throw new Error(result.message || 'Status konnte nicht geändert werden.');
            card.dataset.currentStatus = newStatus;
            board.querySelector('[data-kanban-status="' + newStatus + '"] .todo-kanban-dropzone').prepend(card);
            var select = card.querySelector('[data-kanban-status-select]');
            if (select) select.value = newStatus;
            refreshColumns();
            return true;
        } catch (error) {
            window.alert(error.message);
            var select = card.querySelector('[data-kanban-status-select]');
            if (select) select.value = oldStatus;
            return false;
        } finally {
            card.classList.remove('is-moving');
        }
    }

    board.querySelectorAll('.todo-kanban-card[draggable="true"]').forEach(function (card) {
        card.addEventListener('dragstart', function (event) {
            draggedCard = card;
            sourceZone = card.closest('.todo-kanban-dropzone');
            card.classList.add('is-moving');
            event.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', function () {
            card.classList.remove('is-moving');
            draggedCard = null;
            sourceZone = null;
            board.querySelectorAll('.is-drag-over').forEach(function (el) { el.classList.remove('is-drag-over'); });
        });
    });
    board.querySelectorAll('.todo-kanban-card[data-task-url]').forEach(function (card) {
        card.addEventListener('click', function (event) {
            if (event.target.closest('a, button, input, select, textarea, label, form')) return;
            window.location.href = card.dataset.taskUrl;
        });
        card.addEventListener('keydown', function (event) {
            if ((event.key === 'Enter' || event.key === ' ') &&
                !event.target.closest('a, button, input, select, textarea, label, form')) {
                event.preventDefault();
                window.location.href = card.dataset.taskUrl;
            }
        });
    });
    board.querySelectorAll('.todo-kanban-column').forEach(function (column) {
        column.addEventListener('dragover', function (event) { if (draggedCard) { event.preventDefault(); column.classList.add('is-drag-over'); } });
        column.addEventListener('dragleave', function () { column.classList.remove('is-drag-over'); });
        column.addEventListener('drop', async function (event) {
            event.preventDefault();
            column.classList.remove('is-drag-over');
            if (!draggedCard) return;
            var card = draggedCard;
            var oldZone = sourceZone;
            var targetZone = column.querySelector('.todo-kanban-dropzone');
            var beforeCard = cardAfterPointer(targetZone, event.clientY);
            var changed = await changeStatus(card, column.dataset.kanbanStatus, false);
            if (!changed) return;
            if (beforeCard) targetZone.insertBefore(card, beforeCard); else targetZone.appendChild(card);
            refreshColumns();
            try {
                await saveOrder(targetZone);
                if (oldZone && oldZone !== targetZone) await saveOrder(oldZone);
            } catch (error) {
                window.alert(error.message);
            }
        });
    });
    board.querySelectorAll('[data-kanban-status-select]').forEach(function (select) {
        select.addEventListener('change', function () { changeStatus(select.closest('.todo-kanban-card'), select.value, false); });
    });
})();
JS
);
?>
