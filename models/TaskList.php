<?php

namespace humhub\modules\todo\models;

use humhub\modules\space\models\Space;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class TaskList extends ActiveRecord
{
    private const COLORS = [
        '#92b900', '#bf4a0d', '#d99000', '#238b82',
        '#c21875', '#102b8f', '#218a16', '#6f42c1',
        '#0d6efd', '#6c757d',
    ];

    public static function tableName(): string
    {
        return 'todo_task_list';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => fn() => date('Y-m-d H:i:s'),
            ],
            BlameableBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['space_id', 'name'], 'required'],
            [['space_id', 'sort_order'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'trim'],
            [['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'],
            [['name'], 'unique', 'targetAttribute' => ['space_id', 'name'], 'message' => 'Diese Aufgabenliste gibt es in diesem Space bereits.'],
        ];
    }

    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }

    public function getTasks()
    {
        return $this->hasMany(Task::class, ['task_list_id' => 'id']);
    }

    public static function findForSpace(int $spaceId): array
    {
        return static::find()->where(['space_id' => $spaceId])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all();
    }

    public static function findOrCreateForSpace(int $spaceId, string $name): ?self
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $existing = static::find()->where(['space_id' => $spaceId])->andWhere(['name' => $name])->one();
        if ($existing) {
            return $existing;
        }

        $count = (int) static::find()->where(['space_id' => $spaceId])->count();
        $model = new static([
            'space_id' => $spaceId,
            'name' => $name,
            'color' => self::COLORS[$count % count(self::COLORS)],
            'sort_order' => ($count + 1) * 10,
        ]);

        if (!$model->save()) {
            Yii::error('ToDo task list could not be created: ' . implode('; ', $model->getErrorSummary(true)), __METHOD__);
            return null;
        }

        return $model;
    }
}
