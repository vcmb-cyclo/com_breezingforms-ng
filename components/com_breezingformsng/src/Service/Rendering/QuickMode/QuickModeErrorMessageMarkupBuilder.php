<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the Bootstrap error-message container shared by two renderers. */
final class QuickModeErrorMessageMarkupBuilder
{
    public static function build(mixed $alertClass, mixed $errorClass, string $newline): string
    {
        return '<div class="bfErrorMessage ' . (string) $alertClass . ' ' . (string) $errorClass
            . '" style="display:none"></div>' . $newline;
    }
}
