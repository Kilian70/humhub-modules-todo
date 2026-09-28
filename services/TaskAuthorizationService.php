<?php

namespace humhub\modules\todo\services;

/** Central, testable authorization rules for an individual task. */
final class TaskAuthorizationService
{
    public static function canManage(bool $canEditAll, bool $isCreator): bool
    {
        return $canEditAll || $isCreator;
    }

    public static function canWorkOn(bool $canEditAll, bool $isCreator, bool $isAssigned): bool
    {
        return self::canManage($canEditAll, $isCreator) || $isAssigned;
    }

    public static function canDelete(bool $canDeleteAll, bool $isCreator): bool
    {
        return $canDeleteAll || $isCreator;
    }
}
