<?php

namespace humhub\modules\todo\models;

use yii\db\ActiveRecord;

class TaskUser extends ActiveRecord
{
    public static function tableName()
    {
        return 'todo_task_user';
    }
}