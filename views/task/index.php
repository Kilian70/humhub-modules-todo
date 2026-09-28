<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use humhub\modules\user\widgets\Image as UserImage;

$groupBy = $groupBy ?? Yii::$app->request->get('group', 'list');
$isMy = Yii::$app->request->get('my');
$currentDone = Yii::$app->request->get('done');

$taskRow = function ($task, bool $showUsers = true) use ($contentContainer, $currentDone, $isMy, $groupBy) {
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
        'in_bearbeitung' => 'IN BEARBEITUNG',
        'geschlossen' => 'GESCHLOSSEN',
        default => 'OFFEN',
    };
    ?>
    <?php $taskViewUrl = $contentContainer->createUrl('/todo/task/view', ['id' => $task->id]); ?>
    <div class="d-flex align-items-center gap-2 px-2 py-2 border-top todo-list-row"
         role="link"
         tabindex="0"
         data-task-url="<?= Html::encode($taskViewUrl) ?>"
         style="cursor:pointer;">
        <div class="flex-grow-1 min-width-0">
            <div class="d-flex flex-wrap align-items-center gap-1">
                <span class="fw-semibold text-break"><?= Html::encode($task->title) ?></span>
                <span class="<?= $priorityClass ?>"><?= ucfirst(Html::encode($task->priority)) ?></span>
                <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
                <?php if ($isOverdue): ?><span class="badge bg-danger">ÜBERFÄLLIG</span><?php endif; ?>
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

        <?php if ($task->canManage() || $task->canDelete()): ?>
        <div class="d-flex gap-1 flex-shrink-0" data-task-actions>
            <?php if ($task->canManage()): ?>
            <?= Html::a(
                '<i class="fa fa-pencil"></i>',
                $contentContainer->createUrl('/todo/task/update', ['id' => $task->id]),
                [
                    'class' => 'btn btn-xs btn-outline-primary',
                    'title' => 'Bearbeiten',
                    'aria-label' => 'Bearbeiten',
                    'onclick' => 'event.stopPropagation();',
                    'onkeydown' => 'event.stopPropagation();',
                ]
            ) ?>
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
                    'title' => 'Löschen',
                    'aria-label' => 'Löschen',
                    'data-confirm' => 'Wirklich löschen?',
                    'data-method' => 'post',
                    'onclick' => 'event.stopPropagation();',
                    'onkeydown' => 'event.stopPropagation();',
                ]
            ) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
};
?>

<div class="panel panel-default">
    <div class="panel-heading d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <strong style="white-space:nowrap;">ToDo Liste</strong>
            <form method="get" action="<?= $contentContainer->createUrl('/todo/search/index') ?>" class="d-flex gap-1 align-items-center">
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Suche..." style="width:160px">
                <button class="btn btn-sm btn-outline-primary"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="d-flex gap-1">
            <?php if ($contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\EditTasks())): ?>
                <?= Html::a('Aufgabenlisten verwalten', $contentContainer->createUrl('/todo/task-list/index'), ['class' => 'btn btn-default btn-sm']) ?>
            <?php endif; ?>
            <?= Html::a('Vorlagen', $contentContainer->createUrl('/todo/template/index'), ['class' => 'btn btn-default btn-sm']) ?>
            <?php if ($contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\CreateTasks())): ?>
                <?= Html::a('Neue Aufgabe', $contentContainer->createUrl('/todo/task/create'), ['class' => 'btn btn-success btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="mb-2 d-flex flex-wrap gap-1">
            <?= Html::a('Alle Tasks', $contentContainer->createUrl('/todo/task/index', ['group' => $groupBy]), ['class' => 'btn btn-sm ' . (!$isMy && !$currentDone ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a('Meine Tasks', $contentContainer->createUrl('/todo/task/index', ['my' => 1, 'done' => $currentDone, 'group' => $groupBy]), ['class' => 'btn btn-sm ' . ($isMy ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a('Erledigt', $contentContainer->createUrl('/todo/task/index', ['done' => 1, 'my' => $isMy, 'group' => $groupBy]), ['class' => 'btn btn-sm ' . ($currentDone ? 'btn-primary' : 'btn-outline-secondary')]) ?>
        </div>
        <div class="mb-3 d-flex flex-wrap gap-1">
            <?= Html::a('Nach Aufgabenliste', $contentContainer->createUrl('/todo/task/index', ['group' => 'list', 'my' => $isMy, 'done' => $currentDone]), ['class' => 'btn btn-sm ' . ($groupBy === 'list' ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a('Nach Datum', $contentContainer->createUrl('/todo/task/index', ['group' => 'date', 'my' => $isMy, 'done' => $currentDone]), ['class' => 'btn btn-sm ' . ($groupBy === 'date' ? 'btn-primary' : 'btn-outline-secondary')]) ?>
            <?= Html::a('Nach Zuständig', $contentContainer->createUrl('/todo/task/index', ['group' => 'user', 'my' => $isMy, 'done' => $currentDone]), ['class' => 'btn btn-sm ' . ($groupBy === 'user' ? 'btn-primary' : 'btn-outline-secondary')]) ?>
        </div>

        <?php if ($groupBy === 'list'): ?>
            <?php foreach ($tasksByList as $group): ?>
                <?php
                $list = $group['list'];
                $groupTasks = $group['tasks'];
                if (empty($groupTasks) && !$list) continue;
                $name = $list ? $list->name : 'Unsortiert';
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
                            <div class="text-muted small p-2">Noch keine <?= $currentDone ? 'erledigten ' : '' ?>Aufgaben in dieser Liste.</div>
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
                <?php if (empty($tasks)): ?><div class="text-muted p-3">Keine Aufgaben vorhanden.</div><?php endif; ?>
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
JS
);
?>
