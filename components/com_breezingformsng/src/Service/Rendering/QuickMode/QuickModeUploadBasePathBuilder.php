<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Resolves the base path used by the historical QuickMode uploader. */
final class QuickModeUploadBasePathBuilder
{
    public function build(string $baseUri): string
    {
        $base = explode('/', $baseUri);
        $administratorIndex = count($base) - 2;

        if (isset($base[$administratorIndex]) && $base[$administratorIndex] === 'administrator') {
            unset($base[$administratorIndex]);
            $base = array_values($base);
        }

        return implode('/', $base);
    }
}
