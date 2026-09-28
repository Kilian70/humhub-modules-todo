<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\user\widgets\Image as UserImage;
use yii\helpers\Url;

$this->title = 'Aufgabenübersicht';
$filterUrl = static fn(array $changes = []) => array_merge(['/todo/overview/index'], $filters, $changes);
$statusLabels = ['offen' => 'Offen', 'in_bearbeitung' => 'In Bearbeitung', 'geschlossen' => 'Geschlossen'];
$priorityLabels = ['niedrig' => 'Niedrig', 'mittel' => 'Mittel', 'hoch' => 'Hoch'];
?>

<div class="panel panel-default">
    <div class="panel-heading d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong><i class="fa fa-tasks"></i> Aufgabenübersicht über alle Spaces</strong>
        <span class="text-muted small"><?= (int) $stats['total'] ?> Treffer</span>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= Url::to(['/todo/overview/index']) ?>">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label for="todo-overview-keyword">Suche</label>
                    <input id="todo-overview-keyword" class="form-control" name="keyword" value="<?= Html::encode($filters['keyword']) ?>" placeholder="Titel oder Beschreibung">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-scope">Zuständigkeit</label>
                    <select id="todo-overview-scope" class="form-control" name="scope">
                        <?php foreach (['mine' => 'Mir zugeordnet oder erstellt', 'assigned' => 'Mir zugeordnet', 'created' => 'Von mir erstellt', 'all' => 'Alle sichtbaren'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['scope'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-space">Space</label>
                    <select id="todo-overview-space" class="form-control" name="space_id">
                        <option value="0">Alle Spaces</option>
                        <?php foreach ($spaces as $space): ?>
                            <option value="<?= (int) $space->id ?>" <?= (int) $filters['space_id'] === (int) $space->id ? 'selected' : '' ?>><?= Html::encode($space->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-status">Status</label>
                    <select id="todo-overview-status" class="form-control" name="status">
                        <?php foreach (['active' => 'Offene und laufende', 'offen' => 'Offen', 'in_bearbeitung' => 'In Bearbeitung', 'geschlossen' => 'Geschlossen', 'all' => 'Alle Status'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-priority">Priorität</label>
                    <select id="todo-overview-priority" class="form-control" name="priority">
                        <option value="all">Alle</option>
                        <?php foreach ($priorityLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['priority'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <input type="hidden" name="focus" value="<?= Html::encode($filters['focus']) ?>">
            <div class="d-flex flex-wrap gap-1 mt-2">
                <button class="btn btn-primary"><i class="fa fa-filter"></i> Filtern</button>
                <?= Html::a('Zurücksetzen', ['/todo/overview/index'], ['class' => 'btn btn-default']) ?>
            </div>
        </form>
    </div>
</div>

<div class="d-flex flex-wrap gap-1 mb-3">
    <?php foreach ([
        'all' => ['Alle', $stats['total'], 'btn-default'],
        'overdue' => ['Überfällig', $stats['overdue'], 'btn-danger'],
        'soon' => ['Nächste 7 Tage', $stats['soon'], 'btn-warning'],
        'blocked' => ['Blockiert', $stats['blocked'], 'btn-info'],
        'subtasks' => ['Unteraufgaben', null, 'btn-default'],
    ] as $focus => [$label, $count, $buttonClass]): ?>
        <?= Html::a(Html::encode($label) . ($count === null ? '' : ' (' . (int) $count . ')'), $filterUrl(['focus' => $focus]), [
            'class' => 'btn btn-sm ' . ($filters['focus'] === $focus ? $buttonClass : 'btn-outline-secondary'),
        ]) ?>
    <?php endforeach; ?>
</div>

<div class="panel panel-default">
    <?php if (empty($tasks)): ?>
        <div class="panel-body text-muted">Für diese Filter wurden keine Aufgaben gefunden.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($tasks as $task): ?>
                <?php
                $space = $task->content ? $task->content->container : null;
                if (!$space instanceof Space) continue;
                $isOverdue = $task->status !== 'geschlossen' && $task->due_date && $task->due_date < date('Y-m-d');
                $isBlocked = $task->status !== 'geschlossen' && $task->getOpenBlockingTasks()->exists();
                ?>
                <a class="list-group-item" href="<?= Html::encode($space->createUrl('/todo/task/view', ['id' => $task->id])) ?>">
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <strong><?= Html::encode($task->title) ?></strong>
                        <span class="label label-default"><?= Html::encode($statusLabels[$task->status] ?? $task->status) ?></span>
                        <span class="label <?= $task->priority === 'hoch' ? 'label-danger' : ($task->priority === 'mittel' ? 'label-warning' : 'label-success') ?>"><?= Html::encode($priorityLabels[$task->priority] ?? $task->priority) ?></span>
                        <?php if ($isOverdue): ?><span class="label label-danger">ÜBERFÄLLIG</span><?php endif; ?>
                        <?php if ($isBlocked): ?><span class="label label-info"><i class="fa fa-lock"></i> BLOCKIERT</span><?php endif; ?>
                        <?php if ($task->parent_task_id): ?><span class="label label-default"><i class="fa fa-level-up"></i> UNTERAUFGABE</span><?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1">
                        <strong><?= Html::encode($space->name) ?></strong>
                        <?php if ($task->due_date): ?> · Fällig <?= Yii::$app->formatter->asDate($task->due_date, 'short') ?><?php endif; ?>
                        <?php if ($task->parentTask): ?> · Hauptaufgabe: <?= Html::encode($task->parentTask->title) ?><?php endif; ?>
                    </div>
                    <?php if (!empty($task->users)): ?>
                        <div class="small text-muted mt-1">
                            <?php foreach ($task->users as $user): ?>
                                <?= UserImage::widget(['user' => $user, 'width' => 18]) ?> <?= Html::encode($user->displayName) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($truncated): ?>
    <div class="alert alert-info">Es werden höchstens <?= \humhub\modules\todo\services\OverviewTaskService::MAX_RESULTS ?> Aufgaben angezeigt. Bitte Filter verwenden.</div>
<?php endif; ?>
