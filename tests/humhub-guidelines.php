<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$manifest = json_decode(file_get_contents($root . '/module.json'), true, 512, JSON_THROW_ON_ERROR);

$checks = [
    'module metadata keywords' => !empty($manifest['keywords']) && is_array($manifest['keywords']),
    'module requirement check' => is_file($root . '/requirements.php'),
    'HumHub migration base class' => true,
    'translated permission labels' => true,
    'Codeception configuration' => is_file($root . '/tests/codeception.yml')
        && is_file($root . '/tests/codeception/unit.suite.yml')
        && is_file($root . '/tests/config/test.php'),
    'module enable integration' => str_contains(file_get_contents($root . '/Module.php'), 'ContentContainerModuleManager::setDefaultState')
        && str_contains(file_get_contents($root . '/Module.php'), 'ContentContainerModuleState::STATE_DISABLED'),
    'no unused installer hook' => !is_file($root . '/Installer.php'),
];

foreach (glob($root . '/migrations/*.php') as $migration) {
    if (!str_contains(file_get_contents($migration), 'use humhub\\components\\Migration;')) {
        $checks['HumHub migration base class'] = false;
    }
}

foreach (glob($root . '/permissions/*.php') as $permission) {
    $source = file_get_contents($permission);
    if (!str_contains($source, 'function getTitle()') || !str_contains($source, 'function getDescription()') || !str_contains($source, 'Yii::t(')) {
        $checks['translated permission labels'] = false;
    }
}

foreach ($checks as $name => $passed) {
    if (!$passed) {
        fwrite(STDERR, "HumHub guideline check failed: {$name}\n");
        exit(1);
    }
}

echo 'HumHub guideline checks passed for ' . count($checks) . " cases.\n";
