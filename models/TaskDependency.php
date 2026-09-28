<?php

namespace humhub\modules\todo\models;

use yii\db\ActiveRecord;

class TaskDependency extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'todo_task_dependency';
    }

    public function rules(): array
    {
        return [
            [['task_id', 'blocking_task_id'], 'required'],
            [['task_id', 'blocking_task_id'], 'integer'],
            [['task_id', 'blocking_task_id'], 'unique', 'targetAttribute' => ['task_id', 'blocking_task_id']],
        ];
    }
}
