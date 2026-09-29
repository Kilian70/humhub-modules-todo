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

$source = file_get_contents(__DIR__ . '/../services/TaskExportService.php');
foreach (["php://temp/maxmemory:", "->each(self::BATCH_SIZE)", "exportToStream"] as $required) {
    if (!str_contains($source, $required)) {
        fwrite(STDERR, "CSV streaming protection is missing: {$required}\n");
        exit(1);
    }
}

$controller = file_get_contents(__DIR__ . '/../controllers/TaskController.php');
if (!str_contains($controller, 'sendStreamAsFile') || str_contains($controller, 'sendContentAsFile($content, $filename')) {
    fwrite(STDERR, "CSV controller does not stream the export\n");
    exit(1);
}

echo "CSV export safety passed for " . count($cases) . " cases.\n";
