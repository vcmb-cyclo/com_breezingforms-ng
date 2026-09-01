<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

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
        $fieldSelector = json_encode(
            '[name="ff_nm_' . $recordName . '[]"]',
            JSON_THROW_ON_ERROR
        );

        return 'JQuery(document).ready(function(){'
            . 'if(typeof bf_signaturePad' . $elementId . ' != "undefined"){'
            . 'if(' . (strlen($encodedImage) > 0 ? 'true' : 'false') . '){'
            . 'JQuery(' . $fieldSelector . ').val(' . json_encode($dataUrl, JSON_THROW_ON_ERROR) . ')' . "\n"
            . 'bf_signaturePad' . $elementId . '.fromDataURL(' . json_encode($dataUrl, JSON_THROW_ON_ERROR) . ');'
            . '}'
            . '}'
            . '});';
    }
}
