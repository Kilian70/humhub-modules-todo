<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/services/UploadLimitService.php';

use humhub\modules\todo\services\UploadLimitService;

$cases = [
    '8M' => 8 * 1024 * 1024,
    '32M' => 32 * 1024 * 1024,
    '1G' => 1024 * 1024 * 1024,
    '512K' => 512 * 1024,
    '0' => 0,
    '' => 0,
];

foreach ($cases as $value => $expected) {
    $actual = UploadLimitService::iniSizeToBytes((string) $value);
    if ($actual !== $expected) {
        fwrite(STDERR, "Unexpected conversion for {$value}: {$actual}\n");
        exit(1);
    }
}

echo "Upload-limit tests passed.\n";
