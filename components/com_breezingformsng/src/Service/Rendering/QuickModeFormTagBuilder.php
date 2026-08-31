<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the QuickMode form opening tag.
 */
final class QuickModeFormTagBuilder
{
    public function build(
        string $action,
        string $formId,
        string $customClass = '',
        string $newline = "\n"
    ): string {
        $params = ' action="' . $action . '"'
            . ' method="post"'
            . ' name="' . $formId . '"'
            . ' id="' . $formId . '"'
            . ' enctype="multipart/form-data"';
        if ($customClass !== '') {
            $params .= ' class="' . $customClass . '"';
        }

        return '<form data-ajax="false"' . $params
            . ' accept-charset="utf-8" onsubmit="return false;" class="bfQuickMode">'
            . $newline;
    }
}
