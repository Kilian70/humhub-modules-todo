<?php

namespace humhub\modules\todo\services;

use DateTime;
use humhub\modules\content\models\Content;
use humhub\modules\todo\models\ChecklistItem;
use humhub\modules\todo\models\Task;
use Throwable;
use Yii;

/**
 * Optional bridge to the official HumHub Calendar module.
 *
 * There is deliberately no hard Composer/module dependency. All calendar
 * classes are resolved only after the Calendar module is present and enabled
 * for the current content container.
 */
class CalendarSyncService
{
    private const CALENDAR_ENTRY_CLASS = 'humhub\modules\calendar\models\CalendarEntry';
    private const CREATE_PERMISSION_CLASS = 'humhub\modules\calendar\permissions\CreateEntry';

    public static function isAvailable($contentContainer): bool
    {
        if (!$contentContainer) {
            return false;
        }

        try {
            $calendar = Yii::$app->moduleManager->getModule('calendar');
            if (!$calendar || !$calendar->getIsEnabled()) {
                return false;
            }

            if (!$contentContainer->moduleManager->isEnabled('calendar')) {
                return false;
            }

            return class_exists(self::CALENDAR_ENTRY_CLASS);
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function canCreate($contentContainer): bool
    {
        if (!self::isAvailable($contentContainer) || !class_exists(self::CREATE_PERMISSION_CLASS)) {
            return false;
        }

        try {
            $permissionClass = self::CREATE_PERMISSION_CLASS;
            return $contentContainer->permissionManager->can(new $permissionClass());
        } catch (Throwable $e) {
            Yii::warning($e, __METHOD__);
            return false;
        }
    }

    /**
     * Synchronize a ToDo task with an all-day CalendarEntry.
     *
     * @return bool True if no action was needed or sync succeeded.
     */
    public static function syncTask(Task $task): bool
    {
        $container = $task->content ? $task->content->container : null;

        if (!$task->sync_to_calendar || !$task->due_date) {
            return self::removeLinkedEntry($task, $container);
        }

        if (!self::isAvailable($container)) {
            return true; // Keep intent; sync can resume when Calendar is enabled again.
        }

        return self::upsertEntry(
            $task,
            $container,
            $task->title,
            $task->description ?: 'ToDo-Aufgabe',
            $task->due_date,
            $task->content ? $task->content->visibility : Content::VISIBILITY_PRIVATE
        );
    }

    /**
     * Synchronize a checklist item with an all-day CalendarEntry.
     */
    public static function syncChecklistItem(ChecklistItem $item): bool
    {
        $task = $item->task;
        $container = $task && $task->content ? $task->content->container : null;

        if (!$item->sync_to_calendar || !$item->due_date) {
            return self::removeLinkedEntry($item, $container);
        }

        if (!self::isAvailable($container)) {
            return true;
        }

        $title = $task ? $task->title . ' – ' . $item->title : $item->title;
        $description = $task
            ? 'Checklistenpunkt der ToDo-Aufgabe: ' . $task->title
            : 'ToDo-Checklistenpunkt';

        return self::upsertEntry(
            $item,
            $container,
            $title,
            $description,
            $item->due_date,
            ($task && $task->content) ? $task->content->visibility : Content::VISIBILITY_PRIVATE
        );
    }

    /**
     * Remove linked calendar entries when a ToDo object is deleted.
     */
    public static function deleteLinkedEntry($record): void
    {
        $container = null;
        if ($record instanceof Task) {
            $container = $record->content ? $record->content->container : null;
        } elseif ($record instanceof ChecklistItem && $record->task) {
            $container = $record->task->content ? $record->task->content->container : null;
        }

        self::removeLinkedEntry($record, $container, true);
    }

    private static function upsertEntry($record, $container, string $title, string $description, string $date, int $visibility): bool
    {
        if (!$container) {
            return false;
        }

        $entryClass = self::CALENDAR_ENTRY_CLASS;
        $entry = null;

        if (!empty($record->calendar_entry_id)) {
            $entry = $entryClass::findOne((int) $record->calendar_entry_id);
        }

        $isNew = !$entry;
        if ($isNew) {
            if (!self::canCreate($container)) {
                Yii::warning('Calendar entry was not created because the user has no Calendar CreateEntry permission.', __METHOD__);
                return false;
            }

            $entry = new $entryClass();
            $entry->content->container = $container;
            $entry->content->visibility = $visibility;
        }

        $start = new DateTime($date . ' 00:00:00');
        $end = (clone $start)->modify('+1 day');

        $entry->title = mb_substr($title, 0, 200);
        $entry->description = $description;
        $entry->all_day = 1;
        $entry->start_datetime = $start->format('Y-m-d H:i:s');
        $entry->end_datetime = $end->format('Y-m-d H:i:s');

        // ToDo deadlines are calendar markers, not events requiring attendance.
        $entry->participation_mode = 0;
        $entry->allow_maybe = 0;
        $entry->allow_decline = 0;

        if (!$entry->save()) {
            Yii::error(
                'ToDo calendar sync failed: ' . implode('; ', $entry->getErrorSummary(true)),
                __METHOD__
            );
            return false;
        }

        if ($isNew || (int) $record->calendar_entry_id !== (int) $entry->id) {
            $record->updateAttributes(['calendar_entry_id' => (int) $entry->id]);
        }

        return true;
    }

    private static function removeLinkedEntry($record, $container = null, bool $forceAttempt = false): bool
    {
        if (empty($record->calendar_entry_id)) {
            return true;
        }

        // If Calendar is merely disabled in the Space, keep the linked event unless
        // the ToDo object is being deleted. This avoids destructive side effects
        // caused only by temporarily disabling a module.
        if (!$forceAttempt && !self::isAvailable($container)) {
            return true;
        }

        if (!class_exists(self::CALENDAR_ENTRY_CLASS)) {
            return true;
        }

        try {
            $entryClass = self::CALENDAR_ENTRY_CLASS;
            $entry = $entryClass::findOne((int) $record->calendar_entry_id);
            if ($entry) {
                $entry->delete();
            }

            if (!$forceAttempt) {
                $record->updateAttributes(['calendar_entry_id' => null]);
            }
            return true;
        } catch (Throwable $e) {
            Yii::error($e, __METHOD__);
            return false;
        }
    }
}
