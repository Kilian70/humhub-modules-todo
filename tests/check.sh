#!/usr/bin/env bash

set -euo pipefail

module_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$module_dir"

find . -type f -name '*.php' -not -path './vendor/*' -print0 \
    | xargs -0 -n1 php -l

composer validate --strict --no-check-publish

php tests/authorization.php
php tests/reminder-policy.php
php tests/recurrence-policy.php
php tests/translation-coverage.php
php tests/export-policy.php

if grep -RInE "due_date = ['\"]{2}|->isModuleEnabled\(|MenuLink::isActiveState\(" --include='*.php' .; then
    echo "Obsolete HumHub API usage or invalid DATE comparison found" >&2
    exit 1
fi

for method in canManage canWorkOn canDelete; do
    if ! grep -q "function ${method}" models/Task.php; then
        echo "Missing task authorization method: ${method}" >&2
        exit 1
    fi
done

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
$taskController = file_get_contents("controllers/TaskController.php");
if (preg_match("/->contentContainer\\([^)]*\\)\\s*->where\\(/", $taskController)) {
    fwrite(STDERR, "A task query overwrites its content-container scope with where()\n");
    exit(1);
}
$overviewSources = file_get_contents("services/OverviewTaskService.php") . file_get_contents("views/overview/index.php");
if (str_contains($overviewSources, "getOpenBlockingTasks()->exists()")) {
    fwrite(STDERR, "Overview contains a per-task blocker query\n");
    exit(1);
}
$reminderSource = file_get_contents("services/ReminderService.php");
if (!str_contains($reminderSource, "Atomically claim this reminder stage") || !str_contains($reminderSource, "releaseClaim")) {
    fwrite(STDERR, "Missing concurrent reminder claim protection\n");
    exit(1);
}
$moduleSource = file_get_contents("Module.php");
$wallEntrySource = file_get_contents("widgets/WallEntry.php");
if (!str_contains($moduleSource, "function getContentClasses") || !str_contains($moduleSource, "new CreateTasks()")) {
    fwrite(STDERR, "Missing permission-aware stream creation menu integration\n");
    exit(1);
}
if (!str_contains($wallEntrySource, "'/todo/task/create'") || !str_contains($wallEntrySource, "EDIT_MODE_NEW_WINDOW")) {
    fwrite(STDERR, "Missing ToDo link in HumHub stream creation menu\n");
    exit(1);
}
foreach (["README.md", "CHANGELOG.md", "LICENSE", "SECURITY.md", "CONTRIBUTING.md"] as $file) {
    if (!is_file($file) || filesize($file) === 0) {
        fwrite(STDERR, "Missing required repository file: {$file}\n");
        exit(1);
    }
}
if (!is_file("resources/module_image.png") || filesize("resources/module_image.png") === 0) {
    fwrite(STDERR, "Missing module image\n");
    exit(1);
}
if (!is_file("migrations/uninstall.php")) {
    fwrite(STDERR, "Missing uninstall migration\n");
    exit(1);
}
foreach ([
    "models/TaskHistory.php",
    "services/TaskHistoryService.php",
    "migrations/m260928_120000_task_history.php",
    "services/ReminderPolicy.php",
    "migrations/m260928_140000_reminder_stages.php",
    "services/RecurrencePolicy.php",
    "services/RecurringTaskService.php",
    "migrations/m260928_160000_recurring_tasks.php",
    "migrations/m260928_180000_task_hierarchy.php",
    "models/TaskDependency.php",
    "services/TaskDependencyService.php",
    "migrations/m260928_200000_task_dependencies.php",
    "notifications/TaskUnblocked.php",
    "services/TaskDuplicationService.php",
    "models/TaskTemplate.php",
    "services/TaskTemplateService.php",
    "controllers/TemplateController.php",
    "migrations/m260928_220000_task_templates.php",
    "services/OverviewTaskService.php",
    "models/MenuSettingsForm.php",
    "controllers/MenuSettingsController.php",
    "views/menu-settings/index.php",
    "models/TaskNotificationPreference.php",
    "services/TaskNotificationPreferenceService.php",
    "migrations/m260929_030000_task_notification_preferences.php",
    "services/TaskExportService.php",
    "views/task/print.php",
] as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing task history component: {$file}\n");
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
