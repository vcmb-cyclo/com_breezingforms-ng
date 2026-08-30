<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the default CAPTCHA error and submit callback values.
 */
final class CaptchaValidationDefaultsBuilder
{
    /**
     * @return array{0: string, 1: string}
     */
    public function build(string $errorMessage): array
    {
        return [
            json_encode(
                $errorMessage,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            ),
            'function bfCheckCaptcha(){if(checkFileExtensions())ff_submitForm2();}',
        ];
    }
}
