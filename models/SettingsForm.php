<?php

namespace humhub\modules\todo\models;

use Yii;
use yii\base\Model;

class SettingsForm extends Model
{
    public $dashboardWidgetEnabled = true;
    public $dashboardWidgetSortOrder = 300;
    public $dashboardWidgetLimit = 5;
    public $remindersEnabled = true;
    public $reminderDaysBefore = 3;
    public $upcomingReminderEnabled = true;
    public $dueReminderEnabled = true;
    public $overdueReminderEnabled = true;

    public function rules(): array
    {
        return [
            [['dashboardWidgetEnabled'], 'boolean'],
            [['remindersEnabled', 'upcomingReminderEnabled', 'dueReminderEnabled', 'overdueReminderEnabled'], 'boolean'],
            [['dashboardWidgetSortOrder'], 'integer', 'min' => 0, 'max' => 10000],
            [['dashboardWidgetLimit'], 'integer', 'min' => 1, 'max' => 20],
            [['reminderDaysBefore'], 'integer', 'min' => 0, 'max' => 30],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'dashboardWidgetEnabled' => 'Dashboard-Widget «Meine ToDos» anzeigen',
            'dashboardWidgetSortOrder' => 'Position im Dashboard',
            'dashboardWidgetLimit' => 'Anzahl Aufgaben im Dashboard',
            'remindersEnabled' => 'Erinnerungen aktivieren',
            'reminderDaysBefore' => 'Vorwarnung in Tagen',
            'upcomingReminderEnabled' => Yii::t('TodoModule.base', 'Vor Fälligkeit erinnern'),
            'dueReminderEnabled' => Yii::t('TodoModule.base', 'Am Fälligkeitstag erinnern'),
            'overdueReminderEnabled' => Yii::t('TodoModule.base', 'Einmal bei Überfälligkeit erinnern'),
        ];
    }

    public function loadSettings(): void
    {
        $settings = Yii::$app->getModule('todo')->settings;
        $this->dashboardWidgetEnabled = (bool) $settings->get('dashboardWidgetEnabled', true);
        $this->dashboardWidgetSortOrder = (int) $settings->get('dashboardWidgetSortOrder', 300);
        $this->dashboardWidgetLimit = (int) $settings->get('dashboardWidgetLimit', 5);
        $this->remindersEnabled = (bool) $settings->get('remindersEnabled', true);
        $this->reminderDaysBefore = (int) $settings->get('reminderDaysBefore', 3);
        $this->upcomingReminderEnabled = (bool) $settings->get('upcomingReminderEnabled', true);
        $this->dueReminderEnabled = (bool) $settings->get('dueReminderEnabled', true);
        $this->overdueReminderEnabled = (bool) $settings->get('overdueReminderEnabled', true);
    }

    public function saveSettings(): void
    {
        $settings = Yii::$app->getModule('todo')->settings;
        $settings->set('dashboardWidgetEnabled', $this->dashboardWidgetEnabled ? 1 : 0);
        $settings->set('dashboardWidgetSortOrder', (int) $this->dashboardWidgetSortOrder);
        $settings->set('dashboardWidgetLimit', (int) $this->dashboardWidgetLimit);
        $settings->set('remindersEnabled', $this->remindersEnabled ? 1 : 0);
        $settings->set('reminderDaysBefore', (int) $this->reminderDaysBefore);
        $settings->set('upcomingReminderEnabled', $this->upcomingReminderEnabled ? 1 : 0);
        $settings->set('dueReminderEnabled', $this->dueReminderEnabled ? 1 : 0);
        $settings->set('overdueReminderEnabled', $this->overdueReminderEnabled ? 1 : 0);
    }
}
