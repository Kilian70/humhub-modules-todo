<?php

namespace humhub\modules\todo\models;

use Yii;
use yii\base\Model;

class SettingsForm extends Model
{
    public $dashboardWidgetEnabled = true;
    public $dashboardWidgetSortOrder = 300;
    public $dashboardWidgetLimit = 5;

    public function rules(): array
    {
        return [
            [['dashboardWidgetEnabled'], 'boolean'],
            [['dashboardWidgetSortOrder'], 'integer', 'min' => 0, 'max' => 10000],
            [['dashboardWidgetLimit'], 'integer', 'min' => 1, 'max' => 20],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'dashboardWidgetEnabled' => 'Dashboard-Widget «Meine ToDos» anzeigen',
            'dashboardWidgetSortOrder' => 'Position im Dashboard',
            'dashboardWidgetLimit' => 'Anzahl Aufgaben im Dashboard',
        ];
    }

    public function loadSettings(): void
    {
        $settings = Yii::$app->getModule('todo')->settings;
        $this->dashboardWidgetEnabled = (bool) $settings->get('dashboardWidgetEnabled', true);
        $this->dashboardWidgetSortOrder = (int) $settings->get('dashboardWidgetSortOrder', 300);
        $this->dashboardWidgetLimit = (int) $settings->get('dashboardWidgetLimit', 5);
    }

    public function saveSettings(): void
    {
        $settings = Yii::$app->getModule('todo')->settings;
        $settings->set('dashboardWidgetEnabled', $this->dashboardWidgetEnabled ? 1 : 0);
        $settings->set('dashboardWidgetSortOrder', (int) $this->dashboardWidgetSortOrder);
        $settings->set('dashboardWidgetLimit', (int) $this->dashboardWidgetLimit);
    }
}
