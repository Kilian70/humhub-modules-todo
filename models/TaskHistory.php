<?php

namespace humhub\modules\todo\models;

use humhub\modules\user\models\User;
use yii\db\ActiveRecord;

class TaskHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'todo_task_history';
    }

    public function rules(): array
    {
        return [
            [['task_id', 'event', 'message', 'created_at'], 'required'],
            [['task_id', 'user_id'], 'integer'],
            [['event'], 'string', 'max' => 50],
            [['message'], 'string', 'max' => 500],
            [['created_at'], 'safe'],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
