<?php

namespace humhub\modules\todo\models;

use yii\db\ActiveRecord;

class TaskNotificationPreference extends ActiveRecord
{
    public static function tableName()
    {
        return 'todo_task_notification_preference';
    }

    public function rules()
    {
        return [
            [['task_id', 'user_id'], 'integer'],
            [['task_id', 'user_id', 'mode'], 'required'],
            ['mode', 'in', 'range' => ['all', 'important', 'reminders', 'muted']],
        ];
    }
}
