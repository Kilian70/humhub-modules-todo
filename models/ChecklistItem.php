<?php

namespace humhub\modules\todo\models;

use humhub\modules\user\models\User;
use humhub\modules\todo\services\CalendarSyncService;
use yii\db\ActiveRecord;

class ChecklistItem extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'todo_checklist_item';
    }

    public function rules(): array
    {
        return [
            [['task_id', 'sort_order', 'completed_by'], 'integer'],
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['is_done', 'sync_to_calendar'], 'boolean'],
            [['completed_at'], 'safe'],
        ];
    }

    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public function getAssignedUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('todo_checklist_item_user', ['checklist_item_id' => 'id']);
    }

    public function afterDelete()
    {
        CalendarSyncService::deleteLinkedEntry($this);
        parent::afterDelete();
    }

    public function getCompletedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'completed_by']);
    }
}
