<?php

declare(strict_types=1);

$humhubRoot = isset($argv[1]) ? realpath($argv[1]) : false;
$humhubVersion = $argv[2] ?? '';
$moduleRoot = dirname(__DIR__);

if ($humhubRoot === false || !is_file($humhubRoot . '/protected/vendor/autoload.php')) {
    fwrite(STDERR, "A prepared HumHub checkout is required.\n");
    exit(1);
}

require $humhubRoot . '/protected/vendor/autoload.php';
require $humhubRoot . '/protected/vendor/yiisoft/yii2/Yii.php';

spl_autoload_register(static function (string $class) use ($moduleRoot, $humhubRoot): void {
    $prefix = 'humhub\\modules\\todo\\';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $path = $moduleRoot . '/' . $relative . '.php';
    } elseif (str_starts_with($class, 'humhub\\')) {
        $relative = str_replace('\\', '/', substr($class, strlen('humhub\\')));
        $path = $humhubRoot . '/protected/humhub/' . $relative . '.php';
    } else {
        return;
    }
    if (is_file($path)) {
        require $path;
    }
});

$manifest = json_decode(file_get_contents($moduleRoot . '/module.json'), true, 512, JSON_THROW_ON_ERROR);
if ($humhubVersion === '' || version_compare($humhubVersion, $manifest['humhub']['minVersion'], '<')) {
    fwrite(STDERR, "HumHub {$humhubVersion} is below the declared minimum version.\n");
    exit(1);
}

foreach ([
    humhub\modules\todo\Module::class,
    humhub\modules\todo\Events::class,
    humhub\modules\todo\models\Task::class,
    humhub\modules\todo\permissions\ViewTasks::class,
    humhub\modules\todo\permissions\CreateTasks::class,
    humhub\modules\todo\permissions\EditTasks::class,
    humhub\modules\todo\permissions\DeleteTasks::class,
] as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, "Cannot load {$class} against HumHub {$humhubVersion}.\n");
        exit(1);
    }
}

$config = require $moduleRoot . '/config.php';
if (($config['id'] ?? null) !== 'todo' || ($config['class'] ?? null) !== humhub\modules\todo\Module::class) {
    fwrite(STDERR, "The HumHub module configuration is invalid.\n");
    exit(1);
}

echo "ToDo classes and configuration are compatible with HumHub {$humhubVersion}.\n";
