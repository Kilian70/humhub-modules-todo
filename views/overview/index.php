<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\user\widgets\Image as UserImage;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = Yii::t('TodoModule.base', 'Aufgabenübersicht');
$filterUrl = static fn(array $changes = []) => array_merge(['/todo/overview/index'], $filters, $changes);
$statusLabels = ['offen' => Yii::t('TodoModule.base', 'Offen'), 'in_bearbeitung' => Yii::t('TodoModule.base', 'In Bearbeitung'), 'geschlossen' => Yii::t('TodoModule.base', 'Geschlossen')];
$priorityLabels = ['niedrig' => Yii::t('TodoModule.base', 'Niedrig'), 'mittel' => Yii::t('TodoModule.base', 'Mittel'), 'hoch' => Yii::t('TodoModule.base', 'Hoch')];
?>

<div class="panel panel-default">
    <div class="panel-heading d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong><i class="fa fa-tasks"></i> <?= Yii::t('TodoModule.base', 'Aufgabenübersicht über alle Spaces') ?></strong>
        <span class="text-muted small"><?= Yii::t('TodoModule.base', '{count} Treffer', ['count' => (int) $totalCount]) ?></span>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= Url::to(['/todo/overview/index']) ?>">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label for="todo-overview-keyword"><?= Yii::t('TodoModule.base', 'Suche') ?></label>
                    <input id="todo-overview-keyword" class="form-control" name="keyword" value="<?= Html::encode($filters['keyword']) ?>" placeholder="<?= Yii::t('TodoModule.base', 'Titel oder Beschreibung') ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-scope"><?= Yii::t('TodoModule.base', 'Zuständigkeit') ?></label>
                    <select id="todo-overview-scope" class="form-control" name="scope">
                        <?php foreach (['mine' => Yii::t('TodoModule.base', 'Mir zugeordnet oder erstellt'), 'assigned' => Yii::t('TodoModule.base', 'Mir zugeordnet'), 'created' => Yii::t('TodoModule.base', 'Von mir erstellt'), 'all' => Yii::t('TodoModule.base', 'Alle sichtbaren')] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['scope'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-space"><?= Yii::t('TodoModule.base', 'Space') ?></label>
                    <select id="todo-overview-space" class="form-control" name="space_id">
                        <option value="0"><?= Yii::t('TodoModule.base', 'Alle Spaces') ?></option>
                        <?php foreach ($spaces as $space): ?>
                            <option value="<?= (int) $space->id ?>" <?= (int) $filters['space_id'] === (int) $space->id ? 'selected' : '' ?>><?= Html::encode($space->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-status"><?= Yii::t('TodoModule.base', 'Status') ?></label>
                    <select id="todo-overview-status" class="form-control" name="status">
                        <?php foreach (['active' => Yii::t('TodoModule.base', 'Offene und laufende'), 'offen' => Yii::t('TodoModule.base', 'Offen'), 'in_bearbeitung' => Yii::t('TodoModule.base', 'In Bearbeitung'), 'geschlossen' => Yii::t('TodoModule.base', 'Geschlossen'), 'all' => Yii::t('TodoModule.base', 'Alle Status')] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="todo-overview-priority"><?= Yii::t('TodoModule.base', 'Priorität') ?></label>
                    <select id="todo-overview-priority" class="form-control" name="priority">
                        <option value="all"><?= Yii::t('TodoModule.base', 'Alle') ?></option>
                        <?php foreach ($priorityLabels as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['priority'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <input type="hidden" name="focus" value="<?= Html::encode($filters['focus']) ?>">
            <div class="d-flex flex-wrap gap-1 mt-2">
                <button class="btn btn-primary"><i class="fa fa-filter"></i> <?= Yii::t('TodoModule.base', 'Filtern') ?></button>
                <?= Html::a(Yii::t('TodoModule.base', 'Zurücksetzen'), ['/todo/overview/index'], ['class' => 'btn btn-default todo-dark-default']) ?>
            </div>
        </form>
    </div>
</div>

<div class="d-flex flex-wrap gap-1 mb-3">
    <?php foreach ([
        'all' => [Yii::t('TodoModule.base', 'Alle'), $stats['total'], 'btn-default'],
        'overdue' => [Yii::t('TodoModule.base', 'Überfällig'), $stats['overdue'], 'btn-danger'],
        'soon' => [Yii::t('TodoModule.base', 'Nächste 7 Tage'), $stats['soon'], 'btn-warning'],
        'blocked' => [Yii::t('TodoModule.base', 'Blockiert'), $stats['blocked'], 'btn-info'],
        'subtasks' => [Yii::t('TodoModule.base', 'Unteraufgaben'), null, 'btn-default'],
    ] as $focus => [$label, $count, $buttonClass]): ?>
        <?= Html::a(Html::encode($label) . ($count === null ? '' : ' (' . (int) $count . ')'), $filterUrl(['focus' => $focus]), [
            'class' => 'btn btn-sm ' . ($filters['focus'] === $focus ? $buttonClass . ($buttonClass === 'btn-default' ? ' todo-dark-default' : '') : 'btn-outline-secondary'),
        ]) ?>
    <?php endforeach; ?>
</div>

<div class="panel panel-default">
    <?php if (empty($tasks)): ?>
        <div class="panel-body text-muted"><?= Yii::t('TodoModule.base', 'Für diese Filter wurden keine Aufgaben gefunden.') ?></div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($tasks as $task): ?>
                <?php
                $space = $task->content ? $task->content->container : null;
                if (!$space instanceof Space) continue;
                $isOverdue = $task->status !== 'geschlossen' && $task->due_date && $task->due_date < date('Y-m-d');
                $isBlocked = $task->status !== 'geschlossen' && isset($blockedTaskIds[(int) $task->id]);
                ?>
                <a class="list-group-item" href="<?= Html::encode($space->createUrl('/todo/task/view', ['id' => $task->id])) ?>">
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <strong><?= Html::encode($task->title) ?></strong>
                        <span class="label label-default"><?= Html::encode($statusLabels[$task->status] ?? $task->status) ?></span>
                        <span class="label <?= $task->priority === 'hoch' ? 'label-danger' : ($task->priority === 'mittel' ? 'label-warning' : 'label-success') ?>"><?= Html::encode($priorityLabels[$task->priority] ?? $task->priority) ?></span>
                        <?php if ($isOverdue): ?><span class="label label-danger"><?= Yii::t('TodoModule.base', 'ÜBERFÄLLIG') ?></span><?php endif; ?>
                        <?php if ($isBlocked): ?><span class="label label-info"><i class="fa fa-lock"></i> <?= Yii::t('TodoModule.base', 'BLOCKIERT') ?></span><?php endif; ?>
                        <?php if ($task->parent_task_id): ?><span class="label label-default"><i class="fa fa-level-up"></i> <?= Yii::t('TodoModule.base', 'UNTERAUFGABE') ?></span><?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1">
                        <strong><?= Html::encode($space->name) ?></strong>
                        <?php if ($task->due_date): ?> · <?= Yii::t('TodoModule.base', 'Fällig') ?> <?= Yii::$app->formatter->asDate($task->due_date, 'short') ?><?php endif; ?>
                        <?php if ($task->parentTask): ?> · <?= Yii::t('TodoModule.base', 'Hauptaufgabe:') ?> <?= Html::encode($task->parentTask->title) ?><?php endif; ?>
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

<?php if ($pagination->totalCount > 0): ?>
    <div class="text-muted small text-center mt-3" role="status">
        <?= Yii::t('TodoModule.base', 'Aufgaben {first}–{last} von {total}', [
            'first' => $pagination->offset + 1,
            'last' => min($pagination->offset + $pagination->limit, $pagination->totalCount),
            'total' => $pagination->totalCount,
        ]) ?>
    </div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pagination]) ?>

<?php $this->registerCss(<<<CSS
.todo-dark-default {
    background:var(--hh-background-color-secondary,#fff);
    color:var(--hh-text-color-main,#333);
    border-color:var(--hh-background3,#ccc);
}
.todo-dark-default:hover,
.todo-dark-default:focus {
    background:var(--hh-background-color-highlight-soft,#f5f5f5);
    color:var(--hh-text-color-highlight,var(--hh-text-color-main,#333));
    border-color:var(--hh-text-color-highlight,#16788a);
}
CSS
); ?>
