<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/**
 * Builds the submit callback used by QuickMode navigation buttons.
 */
final class QuickModeSubmitActionBuilder
{
    public function build(bool $onePage, bool $hasFlashUpload): string
    {
        $submitFunction = $onePage ? 'bf_validate_submit' : 'ff_validate_submit';
        $submitCall = $submitFunction . "(this, 'click')";

        if (!$hasFlashUpload) {
            return $submitCall;
        }

        return "if(typeof bfAjaxObject101 == 'undefined' && typeof bfReCaptchaLoaded == 'undefined')"
            . "{bfDoFlashUpload()}else{"
            . $submitCall . '}';
    }
}
