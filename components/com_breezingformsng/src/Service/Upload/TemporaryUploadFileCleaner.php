<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Upload;

use Joomla\Filesystem\File;

/** Removes expired temporary files created by the chunked uploader. */
final class TemporaryUploadFileCleaner
{
    private const MAX_AGE = 86400;

    public function purge(string $directory, string $suffix, ?int $now = null): void
    {
        if (!is_dir($directory) || !is_readable($directory)) {
            return;
        }

        $now ??= time();
        foreach (new \DirectoryIterator($directory) as $file) {
            if (!$file->isFile() || !is_readable($file->getPathname())) {
                continue;
            }

            if (!$this->isExpiredCandidate($file->getFilename(), $file->getCTime(), $suffix, $now)) {
                continue;
            }

            File::delete($file->getPathname());
        }
    }

    public function isExpiredCandidate(string $filename, int $creationTime, string $suffix, int $now): bool
    {
        return substr_count($filename, '_') >= 4
            && str_ends_with($filename, $suffix)
            && $now - $creationTime >= self::MAX_AGE;
    }
}
