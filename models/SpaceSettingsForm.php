<?php

namespace humhub\modules\todo\models;

use yii\base\Model;

class SpaceSettingsForm extends Model
{
    public $widgetEnabled = true;
    public $widgetSortOrder = 300;
    public $widgetLimit = 5;

    public function rules(): array
    {
        return [
            [['widgetEnabled'], 'boolean'],
            [['widgetSortOrder'], 'integer', 'min' => 0, 'max' => 10000],
            [['widgetLimit'], 'integer', 'min' => 1, 'max' => 20],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'widgetEnabled' => 'Space-Widget «ToDo – Aufgaben» anzeigen',
            'widgetSortOrder' => 'Position im Space',
            'widgetLimit' => 'Anzahl Aufgaben im Space',
        ];
    }

    public function loadSettings($space): void
    {
        $settings = $space->getSettings();
        $this->widgetEnabled = (bool) $settings->get('widgetEnabled', 'todo', true);
        $this->widgetSortOrder = (int) $settings->get('widgetSortOrder', 'todo', 300);
        $this->widgetLimit = (int) $settings->get('widgetLimit', 'todo', 5);
    }

    public function saveSettings($space): void
    {
        $settings = $space->getSettings();
        $settings->set('widgetEnabled', $this->widgetEnabled ? 1 : 0, 'todo');
        $settings->set('widgetSortOrder', (int) $this->widgetSortOrder, 'todo');
        $settings->set('widgetLimit', (int) $this->widgetLimit, 'todo');
    }
}
