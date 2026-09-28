<?php

$root = dirname(__DIR__);
$de = require $root . '/messages/de/base.php';
$en = require $root . '/messages/en/base.php';
$errors = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php' || str_contains($file->getPathname(), '/messages/')) {
        continue;
    }
    $source = file_get_contents($file->getPathname());
    preg_match_all('/Yii::t\(\'TodoModule\.base\',\s*(?:\'((?:\\\\\'|[^\'])*)\'|"((?:\\\\"|[^"])*)")/', $source, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $key = stripcslashes($match[1] !== '' ? $match[1] : $match[2]);
        if (!array_key_exists($key, $de) || !array_key_exists($key, $en)) {
            $errors[] = str_replace($root . '/', '', $file->getPathname()) . ': ' . $key;
        }
    }
}
if ($errors) {
    fwrite(STDERR, "Missing German/English translations:\n" . implode("\n", array_unique($errors)) . "\n");
    exit(1);
}
echo "German/English translation coverage passed.\n";
