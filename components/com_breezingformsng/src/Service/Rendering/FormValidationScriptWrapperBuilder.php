<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Wraps the form-level validation JavaScript in its historical script tag.
 */
final class FormValidationScriptWrapperBuilder
{
    public function open(string $fileExtensionsCheck, string $captchaFunction, string $newline = "\n"): string
    {
        return '<script type="text/javascript">' . $newline
            . '<!--' . $newline
            . $newline
            . $fileExtensionsCheck
            . $captchaFunction;
    }

    public function close(string $newline = "\n"): string
    {
        return '//-->' . $newline . '</script>' . $newline;
    }
}
