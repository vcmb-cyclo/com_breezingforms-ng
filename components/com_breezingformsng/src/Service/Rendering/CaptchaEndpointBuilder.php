<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the endpoints used by CAPTCHA validation scripts.
 */
final class CaptchaEndpointBuilder
{
    /**
     * @return array{image: string, check: string, recaptcha: string}
     */
    public function build(string $root, bool $administrator, int $form): array
    {
        $prefix = $root . ($administrator ? '/administrator' : '');

        return [
            'image' => $prefix . '/index.php?option=com_breezingformsng&bfCaptcha=1&bfMathRandom=',
            'check' => $prefix
                . '/index.php?raw=true&option=com_breezingformsng&checkCaptcha=true&Itemid=0&tmpl=component&value=',
            'recaptcha' => 'index.php?raw=true&option=com_breezingformsng&bfReCaptcha=true&form='
                . $form . '&Itemid=0&tmpl=component',
        ];
    }
}
