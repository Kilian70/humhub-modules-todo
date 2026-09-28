<?php

namespace humhub\modules\todo\models;

use Yii;
use yii\base\Model;

class MenuSettingsForm extends Model
{
    public $menuVisible = true;
    public $menuPosition = 'front';
    public $notificationDefault = 'all';

    public function rules(): array
    {
        return [
            [['menuVisible'], 'boolean'],
            [['menuPosition'], 'in', 'range' => ['front', 'middle', 'back']],
            [['notificationDefault'], 'in', 'range' => ['all', 'important', 'reminders', 'muted']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'menuVisible' => Yii::t('TodoModule.base', '„Meine ToDos“ im Hauptmenü anzeigen'),
            'menuPosition' => Yii::t('TodoModule.base', 'Position im Hauptmenü'),
            'notificationDefault' => Yii::t('TodoModule.base', 'Standard für neue und nicht individuell eingestellte Aufgaben'),
        ];
    }

    public static function loadCurrent(): self
    {
        $settings = self::settings();
        return new self([
            'menuVisible' => (bool) $settings->get('menuVisible', true),
            'menuPosition' => (string) $settings->get('menuPosition', 'front'),
            'notificationDefault' => (string) $settings->get('notificationDefault', 'all'),
        ]);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $settings = self::settings();
        $settings->set('menuVisible', (bool) $this->menuVisible);
        $settings->set('menuPosition', (string) $this->menuPosition);
        $settings->set('notificationDefault', (string) $this->notificationDefault);
        return true;
    }

    public static function settings()
    {
        return Yii::$app->getModule('todo')->settings->contentContainer(Yii::$app->user->identity);
    }
}
