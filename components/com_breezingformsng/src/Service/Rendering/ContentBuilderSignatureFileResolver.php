<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Resolves an existing ContentBuilder signature file.
 */
final class ContentBuilderSignatureFileResolver
{
    public function resolve(string $directory, string $fileName): ?string
    {
        if ($fileName === '') {
            return null;
        }

        $path = $directory . $fileName;

        return file_exists($path) ? $path : null;
    }
}
