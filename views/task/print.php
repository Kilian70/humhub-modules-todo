<?php

use humhub\modules\todo\services\TaskExportService;
use yii\helpers\Html;

/** @var array $tasks */
/** @var object $contentContainer */
/** @var DateTimeImmutable $generatedAt */
?>
<!doctype html>
<html lang="<?= Html::encode(Yii::$app->language) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode(Yii::t('TodoModule.base', 'ToDo-Druckansicht')) ?></title>
    <style>
        body{font-family:Arial,sans-serif;color:#222;margin:24px;font-size:13px}h1{font-size:22px;margin:0 0 4px}.meta{color:#666;margin-bottom:18px}.actions{margin-bottom:18px}.actions button{padding:8px 14px;border:1px solid #777;background:#fff;border-radius:4px;cursor:pointer}table{width:100%;border-collapse:collapse}th,td{padding:7px;border:1px solid #bbb;text-align:left;vertical-align:top}th{background:#f2f2f2}.empty{padding:20px;text-align:center;color:#666}@page{size:landscape;margin:12mm}@media print{body{margin:0;font-size:10pt}.actions{display:none}thead{display:table-header-group}tr{break-inside:avoid}a{color:inherit;text-decoration:none}}
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()"><?= Yii::t('TodoModule.base', 'Drucken oder als PDF speichern') ?></button></div>
    <h1><?= Html::encode(Yii::t('TodoModule.base', 'ToDo-Liste: {space}', ['{space}' => $contentContainer->name])) ?></h1>
    <div class="meta">
        <?= Html::encode(Yii::t('TodoModule.base', 'Erstellt am {date}', ['{date}' => Yii::$app->formatter->asDatetime($generatedAt)])) ?>
        · <?= count($tasks) ?> <?= Yii::t('TodoModule.base', 'Aufgaben') ?>
    </div>
    <table>
        <thead><tr>
            <th><?= Yii::t('TodoModule.base', 'Titel') ?></th>
            <th><?= Yii::t('TodoModule.base', 'Aufgabenliste') ?></th>
            <th><?= Yii::t('TodoModule.base', 'Status') ?></th>
            <th><?= Yii::t('TodoModule.base', 'Priorität') ?></th>
            <th><?= Yii::t('TodoModule.base', 'Fällig') ?></th>
            <th><?= Yii::t('TodoModule.base', 'Zuständig') ?></th>
            <th><?= Yii::t('TodoModule.base', 'Labels') ?></th>
        </tr></thead>
        <tbody>
        <?php if ($tasks === []): ?>
            <tr><td colspan="7" class="empty"><?= Yii::t('TodoModule.base', 'Keine Aufgaben für diese Auswahl gefunden.') ?></td></tr>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= Html::encode($task->title) ?></td>
                    <td><?= Html::encode($task->taskList?->name ?? '') ?></td>
                    <td><?= Html::encode(TaskExportService::statusLabel($task->status)) ?></td>
                    <td><?= Html::encode(TaskExportService::priorityLabel($task->priority)) ?></td>
                    <td><?= Html::encode($task->due_date ? Yii::$app->formatter->asDate($task->due_date) : '') ?></td>
                    <td><?= Html::encode(implode(', ', array_map(static fn($user) => $user->displayName, $task->users))) ?></td>
                    <td><?= Html::encode(implode(', ', array_map(static fn($label) => $label->name, $task->taskLabels))) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
