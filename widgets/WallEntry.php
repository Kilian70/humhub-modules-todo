<?php

namespace humhub\modules\todo\widgets;

use humhub\modules\content\widgets\stream\WallStreamEntryWidget;

class WallEntry extends WallStreamEntryWidget
{
    public $createRoute = '/todo/task/create';

    public $createMode = self::EDIT_MODE_NEW_WINDOW;

    public $createFormSortOrder = 900;

    protected function renderContent()
    {
        return $this->render('wallEntry', [
            'model' => $this->model,
        ]);
    }
}
