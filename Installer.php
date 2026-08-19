<?php

namespace humhub\modules\todo;

use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\space\models\Space;

class Installer
{
    public function onInstall()
    {
        // Default: deaktiviert (0), aktiviert (1), immer aktiviert (2)
        ContentContainerModuleManager::setDefaultState(
            'todo',
            Space::class,
            ContentContainerModuleManager::STATE_ENABLED
        );
    }
}