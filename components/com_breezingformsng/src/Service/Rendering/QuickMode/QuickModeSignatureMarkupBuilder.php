<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the signature canvas and reset control shared by QuickMode themes. */
final class QuickModeSignatureMarkupBuilder
{
    public static function build(
        int $elementId,
        string $fieldName,
        string $buttonAttributes,
        string $buttonLabel,
        bool $includeFieldNameMarker
    ): string {
        $escapedFieldName = htmlspecialchars($fieldName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $marker = $includeFieldNameMarker ? "<span class='bfSignature" . $escapedFieldName . "'></span>" : '';

        return '<div class="bfSignature" id="bfSignature' . $elementId
            . '"><div class="bfSignatureCanvasBorder"><canvas></canvas></div>' . "\n"
            . '<button ' . $buttonAttributes . '><span>' . $buttonLabel . '</span></button>' . "\n"
            . $marker . '</div>';
    }
}
