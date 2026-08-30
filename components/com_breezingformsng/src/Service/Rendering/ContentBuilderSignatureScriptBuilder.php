<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the JavaScript used to restore a ContentBuilder signature.
 */
final class ContentBuilderSignatureScriptBuilder
{
    /**
     * Build the signature restoration script from an already encoded image.
     */
    public function build(string $recordName, int $elementId, string $encodedImage): string
    {
        $dataUrl = 'data:image/png;base64,' . $encodedImage;

        return 'JQuery(document).ready(function(){'
            . 'if(typeof bf_signaturePad' . $elementId . ' != "undefined"){'
            . 'if(' . (strlen($encodedImage) > 0 ? 'true' : 'false') . '){'
            . 'JQuery("[name=\"ff_nm_' . $recordName . '[]\"]").val(' . json_encode($dataUrl) . ')' . "\n"
            . 'bf_signaturePad' . $elementId . '.fromDataURL(' . json_encode($dataUrl) . ');'
            . '}'
            . '}'
            . '});';
    }
}
