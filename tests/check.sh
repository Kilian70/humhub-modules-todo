#!/usr/bin/env bash

set -euo pipefail

module_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$module_dir"

find . -type f -name '*.php' -not -path './vendor/*' -print0 \
    | xargs -0 -n1 php -l

composer validate --strict --no-check-publish

php -r '
$module = json_decode(file_get_contents("module.json"), true, 512, JSON_THROW_ON_ERROR);
$composer = json_decode(file_get_contents("composer.json"), true, 512, JSON_THROW_ON_ERROR);
$version = $module["version"] ?? "";
if (!preg_match("/^\\d+\\.\\d+\\.\\d+$/", $version)) {
    fwrite(STDERR, "Invalid module version\n");
    exit(1);
}
if (!str_contains(file_get_contents("CHANGELOG.md"), "## {$version} ")) {
    fwrite(STDERR, "Current module version is missing from CHANGELOG.md\n");
    exit(1);
}
if (($composer["require"]["php"] ?? null) !== ">=8.2") {
    fwrite(STDERR, "Unexpected PHP requirement\n");
    exit(1);
}
if (($module["id"] ?? null) !== "todo") {
    fwrite(STDERR, "Unexpected module id\n");
    exit(1);
}
foreach (["README.md", "CHANGELOG.md", "LICENSE", "SECURITY.md", "CONTRIBUTING.md"] as $file) {
    if (!is_file($file) || filesize($file) === 0) {
        fwrite(STDERR, "Missing required repository file: {$file}\n");
        exit(1);
    }
}
foreach (["de", "en"] as $language) {
    $messages = require "messages/{$language}/base.php";
    if (!is_array($messages) || $messages === []) {
        fwrite(STDERR, "Missing translations for {$language}\n");
        exit(1);
    }
}
'
