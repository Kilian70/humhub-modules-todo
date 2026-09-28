<?php

namespace humhub\modules\todo\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class TaskLabel extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'todo_task_label';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => fn() => date('Y-m-d H:i:s')],
            BlameableBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['space_id', 'name'], 'required'],
            [['space_id', 'sort_order'], 'integer'],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 60],
            [['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'],
            [['name'], 'unique', 'targetAttribute' => ['space_id', 'name'], 'message' => 'Dieses Label gibt es in diesem Space bereits.'],
        ];
    }

    public function getTasks()
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
            ->viaTable('todo_task_label_map', ['label_id' => 'id']);
    }

    public static function findForSpace(int $spaceId): array
    {
        return static::find()->where(['space_id' => $spaceId])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all();
    }

    /** Returns the better contrasting text color for the configured label color. */
    public function getTextColor(): string
    {
        $hex = ltrim((string) $this->color, '#');
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '#ffffff';
        }

        $channels = array_map(static function (string $value): float {
            $channel = hexdec($value) / 255;
            return $channel <= 0.04045
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        }, str_split($hex, 2));
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
        $whiteContrast = 1.05 / ($luminance + 0.05);
        $darkContrast = ($luminance + 0.05) / 0.066;

        return $whiteContrast >= $darkContrast ? '#ffffff' : '#212529';
    }
}
