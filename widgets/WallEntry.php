<?php

namespace humhub\modules\todo\widgets;

use humhub\modules\content\widgets\stream\WallStreamEntryWidget;

class WallEntry extends WallStreamEntryWidget
{
    protected function renderContent()
    {
        return $this->render('wallEntry', [
            'model' => $this->model,
        ]);
    }
}