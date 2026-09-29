<?php

namespace humhub\modules\todo\services;

use Yii;
use yii\validators\FileValidator;

final class UploadLimitService
{
    public static function maxFileSize(): int
    {
        $phpLimit = (int) (new FileValidator())->getSizeLimit();
        $humHubLimit = (int) (Yii::$app->getModule('file')?->settings->get('maxFileSize') ?? 0);
        $limits = array_filter([$phpLimit, $humHubLimit, self::maxTotalFileSize()], static fn(int $limit): bool => $limit > 0);

        return $limits === [] ? 0 : min($limits);
    }

    public static function maxRequestSize(): int
    {
        return self::iniSizeToBytes((string) ini_get('post_max_size'));
    }

    /** Leaves room for multipart boundaries and the task's regular form fields. */
    public static function maxTotalFileSize(): int
    {
        $requestLimit = self::maxRequestSize();
        if ($requestLimit <= 0) {
            return 0;
        }

        return max(1, $requestLimit - min(262144, (int) floor($requestLimit * 0.05)));
    }

    public static function requestExceedsPostLimit(?int $contentLength = null): bool
    {
        $limit = self::maxRequestSize();
        $contentLength ??= isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;

        return $limit > 0 && $contentLength > $limit;
    }

    public static function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $number = (float) $value;
        return match (strtolower(substr($value, -1))) {
            'g' => (int) round($number * 1024 * 1024 * 1024),
            'm' => (int) round($number * 1024 * 1024),
            'k' => (int) round($number * 1024),
            default => (int) $number,
        };
    }
}
