<?php

declare(strict_types=1);

require dirname(__DIR__) . '/services/ReminderPolicy.php';

use humhub\modules\todo\services\ReminderPolicy;

$today = '2026-09-28';
$cases = [
    ['2026-10-05', 3, false, false, false, null],
    ['2026-10-01', 3, false, false, false, ReminderPolicy::UPCOMING],
    ['2026-10-01', 3, true, false, false, null],
    ['2026-09-28', 3, false, false, false, ReminderPolicy::DUE],
    ['2026-09-28', 3, false, true, false, null],
    ['2026-09-27', 3, false, false, false, ReminderPolicy::OVERDUE],
    ['2026-09-27', 3, false, false, true, null],
];

foreach ($cases as $index => [$dueDate, $daysBefore, $upcomingSent, $dueSent, $overdueSent, $expected]) {
    $actual = ReminderPolicy::determine(
        $dueDate,
        $today,
        $daysBefore,
        true,
        true,
        true,
        $upcomingSent,
        $dueSent,
        $overdueSent
    );
    if ($actual !== $expected) {
        fwrite(STDERR, "Reminder policy case {$index} failed: expected " . var_export($expected, true)
            . ', got ' . var_export($actual, true) . "\n");
        exit(1);
    }
}

echo "Reminder policy passed for " . count($cases) . " cases.\n";
