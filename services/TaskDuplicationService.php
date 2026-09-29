<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\ChecklistItem;
use humhub\modules\todo\models\Task;
use Yii;

final class TaskDuplicationService
{
    public static function duplicate(Task $source): ?Task
    {
        $copy = new Task([
            'title' => $source->title . ' (Kopie)',
            'description' => $source->description,
            'priority' => $source->priority,
            'status' => 'offen',
            'due_date' => $source->due_date,
            'sync_to_calendar' => $source->sync_to_calendar,
            'task_list_id' => $source->task_list_id,
            'recurrence_type' => $source->recurrence_type,
            'recurrence_interval' => $source->recurrence_interval,
            'recurrence_end_date' => $source->recurrence_end_date,
            'user_ids' => array_map(static fn($user) => $user->guid, $source->users),
            'label_ids' => array_map(static fn($label) => (int) $label->id, $source->taskLabels),
        ]);
        $copy->content->container = $source->content->container;

        if (!$copy->save()) {
            Yii::error('Could not duplicate ToDo: ' . implode('; ', $copy->getErrorSummary(true)), __METHOD__);
            return null;
        }

        foreach ($source->checklistItems as $item) {
            $itemCopy = new ChecklistItem([
                'task_id' => $copy->id,
                'title' => $item->title,
                'is_done' => false,
                'sort_order' => $item->sort_order,
                'due_date' => $item->due_date,
                'sync_to_calendar' => $item->sync_to_calendar,
            ]);
            if (!$itemCopy->save()) {
                Yii::warning('Could not duplicate checklist item: ' . implode('; ', $itemCopy->getErrorSummary(true)), __METHOD__);
                continue;
            }
            foreach ($item->assignedUsers as $user) {
                Yii::$app->db->createCommand()->insert('todo_checklist_item_user', [
                    'checklist_item_id' => $itemCopy->id,
                    'user_id' => $user->id,
                ])->execute();
            }
            CalendarSyncService::syncChecklistItem($itemCopy);
        }

        TaskHistoryService::record($source, 'duplicate_created', Yii::t('TodoModule.base', 'Aufgabe dupliziert: {title}', ['title' => $copy->title]));
        TaskHistoryService::record($copy, 'duplicated', Yii::t('TodoModule.base', 'Als Kopie von «{title}» erstellt', ['title' => $source->title]));
        return $copy;
    }
}
