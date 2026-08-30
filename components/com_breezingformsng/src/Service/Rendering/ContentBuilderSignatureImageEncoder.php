<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Encodes an existing ContentBuilder signature image for a data URL.
 */
final class ContentBuilderSignatureImageEncoder
{
    public function encode(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        return base64_encode($contents === false ? '' : $contents);
    }
}
