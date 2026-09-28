<?php

use yii\helpers\Html;

?>
<div class="panel panel-default">
    <div class="panel-heading d-flex justify-content-between align-items-center">
        <strong><?= Yii::t('TodoModule.base', 'Aufgabenvorlagen') ?></strong>
        <?= Html::a(Yii::t('TodoModule.base', 'Zurück zu ToDo'), $contentContainer->createUrl('/todo/task/index'), ['class' => 'btn btn-sm btn-light']) ?>
    </div>
    <div class="panel-body">
        <?php if (empty($templates)): ?>
            <p class="text-muted mb-0"><?= Yii::t('TodoModule.base', 'Noch keine Vorlagen vorhanden. Öffne eine Aufgabe und wähle „Als Vorlage speichern“.') ?></p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($templates as $template): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= Html::encode($template->title) ?></strong>
                            <div class="small text-muted">
                                <?= Yii::t('TodoModule.base', 'Priorität:') ?> <?= Html::encode(Yii::t('TodoModule.base', ucfirst($template->priority))) ?>
                                · <?= Yii::t('TodoModule.base', '{count} Checklistenpunkte', ['count' => count($template->getChecklistTitles())]) ?>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <?php if ($canCreate): ?>
                                <?= Html::beginForm($contentContainer->createUrl('/todo/template/use', ['id' => $template->id]), 'post', ['class' => 'd-inline']) ?>
                                <?= Html::submitButton('<i class="fa fa-plus"></i> ' . Yii::t('TodoModule.base', 'Aufgabe erstellen'), ['class' => 'btn btn-sm btn-primary']) ?>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                            <?php if ($template->canManage() || $contentContainer->permissionManager->can(new \humhub\modules\todo\permissions\EditTasks())): ?>
                                <?= Html::a(Yii::t('TodoModule.base', 'Bearbeiten'), $contentContainer->createUrl('/todo/template/update', ['id' => $template->id]), ['class' => 'btn btn-sm btn-light']) ?>
                                <?= Html::beginForm($contentContainer->createUrl('/todo/template/delete', ['id' => $template->id]), 'post', ['class' => 'd-inline']) ?>
                                <?= Html::submitButton(Yii::t('TodoModule.base', 'Löschen'), ['class' => 'btn btn-sm btn-danger', 'data-confirm' => Yii::t('TodoModule.base', 'Vorlage wirklich löschen?')]) ?>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
