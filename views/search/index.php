<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

?>

<div class="panel panel-default">

<div class="panel-heading d-flex justify-content-between align-items-center">

<strong><?= Yii::t('TodoModule.base', 'Suche') ?></strong>

<form method="get">

<input
type="text"
name="keyword"
value="<?= Html::encode($keyword ?? '') ?>"
class="form-control"
style="width:200px; display:inline-block;"
placeholder="Suchbegriff..."
>

<button class="btn btn-primary btn-sm">
Suchen
</button>

</form>

</div>

<div class="panel-body">


<?php if (empty($tasks)): ?>

<p><?= Yii::t('TodoModule.base', 'Keine Treffer gefunden.') ?></p>

<?php else: ?>

<table class="table table-hover table-striped">

<thead>

<tr>
<th>ToDo</th>
<th><?= Yii::t('TodoModule.base', 'Priorität') ?></th>
<th><?= Yii::t('TodoModule.base', 'Fällig') ?></th>
</tr>

</thead>

<tbody>

<?php foreach ($tasks as $task): ?>

<tr>

<td>

<?= Html::a(
Html::encode($task->title),
$this->context->contentContainer->createUrl('/todo/task/view', [
'id'=>$task->id
])
) ?>

</td>


<td>

<?= Html::encode($task->priority) ?>

</td>


<td>

<?= $task->due_date
? Yii::$app->formatter->asDate($task->due_date)
: '—'
?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
