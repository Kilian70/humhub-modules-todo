<?php

namespace humhub\modules\todo\services;

use humhub\modules\todo\models\ChecklistItem;
use humhub\modules\todo\models\Task;
use humhub\modules\todo\models\TaskTemplate;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\User;

final class TaskTemplateService
{
    public static function fromTask(Task $task, int $spaceId): TaskTemplate
    {
        return new TaskTemplate([
            'space_id' => $spaceId,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'assignee_guids' => json_encode(array_map(static fn($user) => $user->guid, $task->users)),
            'checklist_text' => implode("\n", array_map(static fn($item) => $item->title, $task->checklistItems)),
        ]);
    }

    public static function createTask(TaskTemplate $template, $container): ?Task
    {
        $validAssigneeGuids = User::find()
            ->select('user.guid')
            ->innerJoin('space_membership sm', 'sm.user_id = user.id')
            ->where([
                'user.guid' => $template->getAssigneeGuids(),
                'sm.space_id' => $container->id,
                'sm.status' => Membership::STATUS_MEMBER,
            ])
            ->column();
        $task = new Task([
            'title' => $template->title,
            'description' => $template->description,
            'priority' => $template->priority,
            'status' => 'offen',
            'user_ids' => $validAssigneeGuids,
        ]);
        $task->content->container = $container;
        if (!$task->save()) {
            return null;
        }
        foreach ($template->getChecklistTitles() as $sort => $title) {
            (new ChecklistItem([
                'task_id' => $task->id,
                'title' => $title,
                'is_done' => false,
                'sort_order' => ($sort + 1) * 10,
            ]))->save();
        }
        TaskHistoryService::record($task, 'template_used', 'Aus Vorlage «' . $template->title . '» erstellt');
        return $task;
    }
}
