<?php

use humhub\modules\file\components\FileManager;
use humhub\modules\file\models\File;
use humhub\modules\todo\models\Task;
use yii\db\Migration;
use yii\helpers\FileHelper;

class m260819_151000_migrate_legacy_files_to_humhub extends Migration
{
    public function safeUp()
    {
        $basePath = Yii::getAlias('@runtime/todo-files');

        if (!is_dir($basePath)) {
            return true;
        }

        $errors = [];

        foreach (scandir($basePath) ?: [] as $taskId) {
            if ($taskId === '.' || $taskId === '..' || !ctype_digit((string) $taskId)) {
                continue;
            }

            $taskPath = $basePath . DIRECTORY_SEPARATOR . $taskId;
            if (!is_dir($taskPath)) {
                continue;
            }

            $task = Task::findOne((int) $taskId);
            if (!$task) {
                continue;
            }

            $manager = new FileManager(['record' => $task]);

            foreach (scandir($taskPath) ?: [] as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }

                $legacyPath = $taskPath . DIRECTORY_SEPARATOR . $name;
                if (!is_file($legacyPath)) {
                    continue;
                }

                try {
                    // Make migration retry-safe if a previous run attached the file
                    // but did not yet remove the legacy copy.
                    $legacyHash = sha1_file($legacyPath);
                    $alreadyMigrated = false;

                    foreach ($manager->find()->andWhere(['file_name' => $name])->all() as $existingFile) {
                        if ($legacyHash && $existingFile->getHash() === $legacyHash) {
                            $alreadyMigrated = true;
                            break;
                        }
                    }

                    if (!$alreadyMigrated) {
                        $file = new File();
                        $file->file_name = $name;
                        $file->title = pathinfo($name, PATHINFO_FILENAME);
                        $file->mime_type = FileHelper::getMimeType($legacyPath) ?: 'application/octet-stream';
                        $file->show_in_stream = 0;

                        if (!$file->save()) {
                            throw new RuntimeException(implode('; ', $file->getErrorSummary(true)));
                        }

                        try {
                            $file->setStoredFile($legacyPath);
                            $manager->attach($file);
                        } catch (Throwable $e) {
                            try {
                                $file->delete();
                            } catch (Throwable $cleanupException) {
                                Yii::error($cleanupException, __METHOD__);
                            }
                            throw $e;
                        }
                    }

                    if (!@unlink($legacyPath)) {
                        throw new RuntimeException('Legacy-Datei konnte nach erfolgreicher Migration nicht gelöscht werden: ' . $legacyPath);
                    }
                } catch (Throwable $e) {
                    Yii::error($e, __METHOD__);
                    $errors[] = $legacyPath . ': ' . $e->getMessage();
                }
            }

            $remaining = array_diff(scandir($taskPath) ?: [], ['.', '..']);
            if (empty($remaining)) {
                @rmdir($taskPath);
            }
        }

        $remainingBase = array_diff(scandir($basePath) ?: [], ['.', '..']);
        if (empty($remainingBase)) {
            @rmdir($basePath);
        }

        if (!empty($errors)) {
            throw new RuntimeException(
                "Nicht alle alten ToDo-Dateien konnten in das HumHub-Dateisystem migriert werden:\n" . implode("\n", $errors)
            );
        }

        return true;
    }

    public function safeDown()
    {
        // The migration intentionally does not copy files back to @runtime.
        // HumHub-managed attachments remain attached to their tasks.
        return true;
    }
}
