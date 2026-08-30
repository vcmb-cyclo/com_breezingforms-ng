<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeCaptchaReloadScriptBuilder
{
    public function build(string $captchaUrl): string
    {
        return "document.getElementById('bfCaptchaEntry').value='';"
            . "document.getElementById('bfCaptchaEntry').focus();"
            . "document.getElementById('ff_capimgValue').src = '" . $captchaUrl
            . "&bfMathRandom=' + Math.random(); return false";
    }
}
