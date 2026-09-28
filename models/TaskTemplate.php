<?php

namespace humhub\modules\todo\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class TaskTemplate extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'todo_task_template';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => new Expression('NOW()')],
            ['class' => BlameableBehavior::class],
        ];
    }

    public function rules(): array
    {
        return [
            [['space_id', 'title'], 'required'],
            [['space_id', 'created_by', 'updated_by'], 'integer'],
            [['description', 'assignee_guids', 'checklist_text'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['priority'], 'in', 'range' => ['niedrig', 'mittel', 'hoch']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => Yii::t('TodoModule.base', 'Vorlagenname / Aufgabentitel'),
            'description' => Yii::t('TodoModule.base', 'Beschreibung'),
            'priority' => Yii::t('TodoModule.base', 'Priorität'),
            'checklist_text' => Yii::t('TodoModule.base', 'Checkliste'),
        ];
    }

    public function canManage(): bool
    {
        return (int) $this->created_by === (int) Yii::$app->user->id;
    }

    public function getAssigneeGuids(): array
    {
        $decoded = json_decode((string) $this->assignee_guids, true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    public function getChecklistTitles(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $this->checklist_text) ?: [])));
    }
}
