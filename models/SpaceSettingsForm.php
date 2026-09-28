<?php

namespace humhub\modules\todo\models;

use Yii;
use yii\base\Model;

class SpaceSettingsForm extends Model
{
    public $widgetEnabled = true;
    public $widgetSortOrder = 300;
    public $widgetLimit = 5;
    public $autoArchiveDays = 0;

    public function rules(): array
    {
        return [
            [['widgetEnabled'], 'boolean'],
            [['widgetSortOrder'], 'integer', 'min' => 0, 'max' => 10000],
            [['widgetLimit'], 'integer', 'min' => 1, 'max' => 20],
            [['autoArchiveDays'], 'in', 'range' => [0, 30, 60, 90]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'widgetEnabled' => Yii::t('TodoModule.base', 'Space-Widget «ToDo – Aufgaben» anzeigen'),
            'widgetSortOrder' => Yii::t('TodoModule.base', 'Position im Space'),
            'widgetLimit' => Yii::t('TodoModule.base', 'Anzahl Aufgaben im Space'),
            'autoArchiveDays' => Yii::t('TodoModule.base', 'Erledigte Aufgaben automatisch archivieren'),
        ];
    }

    public function loadSettings($space): void
    {
        $settings = Yii::$app->getModule('todo')->settings->contentContainer($space);
        $this->widgetEnabled = (bool) $settings->get('widgetEnabled', true);
        $this->widgetSortOrder = (int) $settings->get('widgetSortOrder', 300);
        $this->widgetLimit = (int) $settings->get('widgetLimit', 5);
        $this->autoArchiveDays = (int) $settings->get('autoArchiveDays', 0);
    }

    public function saveSettings($space): void
    {
        $settings = Yii::$app->getModule('todo')->settings->contentContainer($space);
        $settings->set('widgetEnabled', $this->widgetEnabled ? 1 : 0);
        $settings->set('widgetSortOrder', (int) $this->widgetSortOrder);
        $settings->set('widgetLimit', (int) $this->widgetLimit);
        $settings->set('autoArchiveDays', (int) $this->autoArchiveDays);
    }
}
