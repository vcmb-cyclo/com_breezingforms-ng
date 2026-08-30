<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Resolves a safe display name from a ContentBuilder file path.
 */
final class ContentBuilderFileDisplayNameBuilder
{
    public function build(string $wrappedPath): string
    {
        $displayName = htmlspecialchars(basename($wrappedPath), ENT_QUOTES, 'UTF-8');

        return str_replace('&lt;br&gt;', '<br>', $displayName);
    }
}
