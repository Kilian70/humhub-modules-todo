<?php

namespace humhub\modules\todo\models;

use Yii;
use yii\base\Model;

class MenuSettingsForm extends Model
{
    public $menuVisible = true;
    public $menuPosition = 'front';

    public function rules(): array
    {
        return [
            [['menuVisible'], 'boolean'],
            [['menuPosition'], 'in', 'range' => ['front', 'middle', 'back']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'menuVisible' => '„Meine ToDos“ im Hauptmenü anzeigen',
            'menuPosition' => 'Position im Hauptmenü',
        ];
    }

    public static function loadCurrent(): self
    {
        $settings = self::settings();
        return new self([
            'menuVisible' => (bool) $settings->get('menuVisible', true),
            'menuPosition' => (string) $settings->get('menuPosition', 'front'),
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
        return true;
    }

    public static function settings()
    {
        return Yii::$app->getModule('todo')->settings->contentContainer(Yii::$app->user->identity);
    }
}
