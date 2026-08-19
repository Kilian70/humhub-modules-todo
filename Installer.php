<?php

namespace humhub\modules\todo;

use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\space\models\Space;

class Installer
{
    public function onInstall()
    {
        // New installations opt in per Space. This avoids exposing task data or
        // navigation entries before permissions and settings were reviewed.
        ContentContainerModuleManager::setDefaultState(
            'todo',
            Space::class,
            ContentContainerModuleManager::STATE_DISABLED
        );
    }
}
