<?php

declare(strict_types=1);

require dirname(__DIR__) . '/services/TaskAuthorizationService.php';

use humhub\modules\todo\services\TaskAuthorizationService as Policy;

$roles = [
    'creator' => [
        'input' => [false, false, true, false],
        'expected' => [true, true, true],
    ],
    'assigned member' => [
        'input' => [false, false, false, true],
        'expected' => [false, true, false],
    ],
    'unrelated member' => [
        'input' => [false, false, false, false],
        'expected' => [false, false, false],
    ],
    'moderator' => [
        'input' => [true, false, false, false],
        'expected' => [true, true, false],
    ],
    'administrator or owner' => [
        'input' => [true, true, false, false],
        'expected' => [true, true, true],
    ],
    'guest' => [
        'input' => [false, false, false, false],
        'expected' => [false, false, false],
    ],
];

foreach ($roles as $role => $case) {
    [$canEditAll, $canDeleteAll, $isCreator, $isAssigned] = $case['input'];
    $actual = [
        Policy::canManage($canEditAll, $isCreator),
        Policy::canWorkOn($canEditAll, $isCreator, $isAssigned),
        Policy::canDelete($canDeleteAll, $isCreator),
    ];

    if ($actual !== $case['expected']) {
        fwrite(STDERR, sprintf(
            "Authorization failed for %s: expected %s, got %s\n",
            $role,
            json_encode($case['expected']),
            json_encode($actual)
        ));
        exit(1);
    }
}

echo "Task authorization matrix passed for " . count($roles) . " roles.\n";
