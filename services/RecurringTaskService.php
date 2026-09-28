<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\ChecklistItem;
use humhub\modules\todo\models\Task;
use Yii;

final class RecurringTaskService
{
    public static function createNext(Task $source): ?Task
    {
        $nextDate = RecurrencePolicy::nextDate(
            $source->due_date,
            $source->recurrence_type,
            (int) $source->recurrence_interval
        );
        if (!$nextDate || $source->status !== 'geschlossen' || $source->recurrence_generated_at) {
            return null;
        }

        // Atomic claim: only one request may generate the successor.
        $generatedAt = date('Y-m-d H:i:s');
        if (Task::updateAll(
            ['recurrence_generated_at' => $generatedAt],
            ['and', ['id' => $source->id], ['recurrence_generated_at' => null]]
        ) !== 1) {
            return null;
        }
        $source->recurrence_generated_at = $generatedAt;

        if (!RecurrencePolicy::isWithinEndDate($nextDate, $source->recurrence_end_date)) {
            TaskHistoryService::record($source, 'recurrence_ended', 'Wiederholung beendet: Enddatum erreicht');
            return null;
        }

        $next = new Task([
            'title' => $source->title,
            'description' => $source->description,
            'priority' => $source->priority,
            'status' => 'offen',
            'due_date' => $nextDate,
            'sync_to_calendar' => $source->sync_to_calendar,
            'task_list_id' => $source->task_list_id,
            'parent_task_id' => $source->parent_task_id,
            'recurrence_type' => $source->recurrence_type,
            'recurrence_interval' => $source->recurrence_interval,
            'recurrence_end_date' => $source->recurrence_end_date,
            'user_ids' => array_map(static fn($user) => $user->guid, $source->users),
            'label_ids' => array_map(static fn($label) => (int) $label->id, $source->labels),
        ]);
        $next->content->container = $source->content->container;

        if (!$next->save()) {
            Task::updateAll(['recurrence_generated_at' => null], ['id' => $source->id]);
            $source->recurrence_generated_at = null;
            Yii::error('Could not create recurring ToDo: ' . implode('; ', $next->getErrorSummary(true)), __METHOD__);
            return null;
        }

        self::copyChecklist($source, $next);
        TaskHistoryService::record($source, 'recurrence_created', 'Folgeaufgabe für ' . $nextDate . ' erstellt');
        TaskHistoryService::record($next, 'recurrence_created', 'Aus wiederkehrender Aufgabe erstellt');
        return $next;
    }

    private static function copyChecklist(Task $source, Task $next): void
    {
        $dayShift = (new \DateTimeImmutable($source->due_date))->diff(new \DateTimeImmutable($next->due_date))->days;
        foreach ($source->checklistItems as $item) {
            $dueDate = $item->due_date;
            if ($dueDate && $dayShift !== false) {
                $dueDate = (new \DateTimeImmutable($dueDate))->modify('+' . $dayShift . ' days')->format('Y-m-d');
            }
            $copy = new ChecklistItem([
                'task_id' => $next->id,
                'title' => $item->title,
                'is_done' => false,
                'sort_order' => $item->sort_order,
                'due_date' => $dueDate,
                'sync_to_calendar' => $item->sync_to_calendar,
            ]);
            if (!$copy->save()) {
                Yii::warning('Could not copy recurring checklist item: ' . implode('; ', $copy->getErrorSummary(true)), __METHOD__);
                continue;
            }
            foreach ($item->assignedUsers as $user) {
                Yii::$app->db->createCommand()->insert('todo_checklist_item_user', [
                    'checklist_item_id' => $copy->id,
                    'user_id' => $user->id,
                ])->execute();
            }
            CalendarSyncService::syncChecklistItem($copy);
        }
    }
}
