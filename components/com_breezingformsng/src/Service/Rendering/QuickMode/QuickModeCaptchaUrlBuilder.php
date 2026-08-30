<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeCaptchaUrlBuilder
{
    public function build(string $root, bool $administrator): string
    {
        return $root . ($administrator ? '/administrator' : '')
            . '/index.php?option=com_breezingformsng&bfCaptcha=1';
    }
}
