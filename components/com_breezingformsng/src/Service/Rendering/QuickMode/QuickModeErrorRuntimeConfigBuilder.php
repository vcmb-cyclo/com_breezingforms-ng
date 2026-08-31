<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the inline error-handling configuration shared by Bootstrap renderers. */
final class QuickModeErrorRuntimeConfigBuilder
{
    public static function build(bool $showDefaultErrors, bool $pageScoped, string $newline): string
    {
        return 'var bfUseErrorAlerts = false;' . $newline
            . 'var bfShowDefaultErrors = ' . ($showDefaultErrors ? 'true' : 'false') . ';' . $newline
            . 'var bfErrorPageScoped = ' . ($pageScoped ? 'true' : 'false') . ';' . $newline;
    }
}
