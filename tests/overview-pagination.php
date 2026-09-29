<?php

declare(strict_types=1);

$service = file_get_contents(dirname(__DIR__) . '/services/OverviewTaskService.php');
$view = file_get_contents(dirname(__DIR__) . '/views/overview/index.php');

$checks = [
    'pagination object' => str_contains($service, 'new Pagination(['),
    'database offset' => str_contains($service, '->offset($pagination->offset)'),
    'database limit' => str_contains($service, '->limit($pagination->limit)'),
    'database focus filter' => str_contains($service, 'private static function applyFocus'),
    'database blocked filter' => str_contains($service, 'return [\'exists\', $subQuery]'),
    'no fixed result cap' => !str_contains($service, 'MAX_RESULTS'),
    'page navigation' => str_contains($view, 'LinkPager::widget([\'pagination\' => $pagination])'),
    'visible result range' => str_contains($view, 'Aufgaben {first}–{last} von {total}'),
];

foreach ($checks as $name => $passed) {
    if (!$passed) {
        fwrite(STDERR, "Overview pagination check failed: {$name}\n");
        exit(1);
    }
}

echo 'Overview pagination checks passed for ' . count($checks) . " cases.\n";
