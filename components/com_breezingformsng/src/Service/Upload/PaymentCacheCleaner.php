<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Upload;

use Joomla\Filesystem\File;

/** Removes expired files from the payment cache. */
final class PaymentCacheCleaner
{
    private const MAX_AGE = 86400;

    public function purge(string $directory, ?int $now = null): void
    {
        if (!is_dir($directory) || !is_readable($directory)) {
            return;
        }

        $now ??= time();
        foreach (new \DirectoryIterator($directory) as $file) {
            if (!$file->isFile() || !is_readable($file->getPathname())) {
                continue;
            }

            if (!$this->isExpiredCandidate($file->getFilename(), $file->getCTime(), $now)) {
                continue;
            }

            File::delete($file->getPathname());
        }
    }

    public function isExpiredCandidate(string $filename, int $creationTime, int $now): bool
    {
        return count(explode('_', $filename)) === 4
            && $now - $creationTime >= self::MAX_AGE;
    }
}
