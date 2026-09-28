<?php

namespace humhub\modules\todo\services;

final class ReminderPolicy
{
    public const UPCOMING = 'upcoming';
    public const DUE = 'due';
    public const OVERDUE = 'overdue';

    public static function determine(
        string $dueDate,
        string $today,
        int $daysBefore,
        bool $upcomingEnabled,
        bool $dueEnabled,
        bool $overdueEnabled,
        bool $upcomingSent,
        bool $dueSent,
        bool $overdueSent
    ): ?string {
        if ($dueDate < $today) {
            return $overdueEnabled && !$overdueSent ? self::OVERDUE : null;
        }
        if ($dueDate === $today) {
            return $dueEnabled && !$dueSent ? self::DUE : null;
        }

        $warningDate = date('Y-m-d', strtotime($today . ' +' . max(0, $daysBefore) . ' days'));
        if ($daysBefore > 0 && $dueDate <= $warningDate) {
            return $upcomingEnabled && !$upcomingSent ? self::UPCOMING : null;
        }

        return null;
    }
}
