<?php

require_once __DIR__ . '/../services/TaskExportService.php';

use humhub\modules\todo\services\TaskExportService;

$cases = [
    '=SUM(A1:A2)' => "'=SUM(A1:A2)",
    '+cmd' => "'+cmd",
    '-10' => "'-10",
    '@value' => "'@value",
    'Normaler Titel' => 'Normaler Titel',
    '  Text  ' => 'Text',
];

foreach ($cases as $input => $expected) {
    if (TaskExportService::safe($input) !== $expected) {
        fwrite(STDERR, "CSV safety check failed for {$input}\n");
        exit(1);
    }
}

echo "CSV export safety passed for " . count($cases) . " cases.\n";
