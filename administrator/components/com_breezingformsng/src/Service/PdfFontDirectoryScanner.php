<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Administrator\Service;

/** Returns readable regular files from a PDF font directory. */
final class PdfFontDirectoryScanner
{
    public function scan(string $directory): array
    {
        if (!is_dir($directory) || !is_readable($directory)) {
            return [];
        }

        $files = [];
        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isFile() && is_readable($file->getPathname())) {
                $files[] = $file->getFilename();
            }
        }

        return $files;
    }
}
