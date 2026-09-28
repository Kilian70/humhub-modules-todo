<?php

namespace humhub\modules\todo\services;

use DateTimeImmutable;

final class RecurrencePolicy
{
    public const TYPES = ['daily', 'weekly', 'monthly', 'yearly'];

    public static function nextDate(?string $dueDate, ?string $type, int $interval = 1): ?string
    {
        if (!$dueDate || !in_array($type, self::TYPES, true)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate);
        if (!$date || $date->format('Y-m-d') !== $dueDate) {
            return null;
        }

        $interval = max(1, $interval);
        if ($type === 'daily') {
            return $date->modify('+' . $interval . ' days')->format('Y-m-d');
        }
        if ($type === 'weekly') {
            return $date->modify('+' . $interval . ' weeks')->format('Y-m-d');
        }

        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');

        if ($type === 'monthly') {
            $monthIndex = ($year * 12) + $month - 1 + $interval;
            $year = intdiv($monthIndex, 12);
            $month = ($monthIndex % 12) + 1;
        } else {
            $year += $interval;
        }

        $lastDay = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
        return sprintf('%04d-%02d-%02d', $year, $month, min($day, $lastDay));
    }

    public static function isWithinEndDate(string $nextDate, ?string $endDate): bool
    {
        return !$endDate || $nextDate <= $endDate;
    }
}
