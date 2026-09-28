<?php

require dirname(__DIR__) . '/services/RecurrencePolicy.php';

use humhub\modules\todo\services\RecurrencePolicy;

$cases = [
    ['2026-09-28', 'daily', 1, '2026-09-29'],
    ['2026-09-28', 'weekly', 2, '2026-10-12'],
    ['2026-01-31', 'monthly', 1, '2026-02-28'],
    ['2024-01-31', 'monthly', 1, '2024-02-29'],
    ['2024-02-29', 'yearly', 1, '2025-02-28'],
    ['2025-12-31', 'monthly', 2, '2026-02-28'],
];

foreach ($cases as [$date, $type, $interval, $expected]) {
    $actual = RecurrencePolicy::nextDate($date, $type, $interval);
    if ($actual !== $expected) {
        fwrite(STDERR, "$date/$type/$interval: expected $expected, got $actual\n");
        exit(1);
    }
}

if (RecurrencePolicy::nextDate('2026-09-28', null) !== null
    || !RecurrencePolicy::isWithinEndDate('2026-10-01', '2026-10-01')
    || RecurrencePolicy::isWithinEndDate('2026-10-02', '2026-10-01')) {
    fwrite(STDERR, "Recurrence boundary check failed\n");
    exit(1);
}

echo "Recurrence policy checks passed\n";
