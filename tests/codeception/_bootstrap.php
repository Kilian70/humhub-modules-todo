<?php

$moduleRoot = dirname(__DIR__, 2);

spl_autoload_register(static function (string $class) use ($moduleRoot): void {
    $prefix = 'humhub\\modules\\todo\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = $moduleRoot . '/' . $relative . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
