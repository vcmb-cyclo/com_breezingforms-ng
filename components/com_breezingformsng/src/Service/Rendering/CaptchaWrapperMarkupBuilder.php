<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the hidden legacy ReCaptcha wrapper used by classic forms.
 */
final class CaptchaWrapperMarkupBuilder
{
    public function build(bool $legacyWrap, string $newline = "\n"): string
    {
        if (!$legacyWrap) {
            return '';
        }

        return '<table style="display:none;width:100%;" id="bfReCaptchaWrap"><tr><td><div id="bfReCaptchaDiv"></div></td></tr></table>'
            . $newline;
    }
}
