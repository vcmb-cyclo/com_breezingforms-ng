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

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the common plupload configuration object used by QuickMode. */
final class QuickModeUploadConfigurationBuilder
{
    public function build(
        int $formId,
        string $fieldName,
        string $ticket,
        string $baseUrl,
        int $elementId,
        string $runtimes,
        string $extensions,
        string $multipleSelection,
        string $chooseFileLabel,
        string $newline = "\n",
        ?string $uploadUrl = null
    ): string {
        $indent = '                                                                ';
        $fieldIndent = $indent . '        ';

        return $indent . 'var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/i) ? true : false );'
            . $newline . $indent . 'var uploader = new plupload.Uploader({'
            . $newline . '                                                                        max_retries: 10,'
            . $newline . $fieldIndent . 'multi_selection: ' . $multipleSelection . ','
            . $newline . $fieldIndent . 'unique_names: iOS,'
            . $newline . $fieldIndent . "chunk_size: '100kb',"
            . $newline . $fieldIndent . "runtimes : '" . $runtimes . "',"
            . $newline . $fieldIndent . "browse_button : 'bfPickFiles" . $elementId . "',"
            . $newline . $fieldIndent . "container: 'bfUploadContainer" . $elementId . "',"
            . $newline . $fieldIndent . "file_data_name: 'Filedata',"
            . $newline . $fieldIndent . "multipart_params: { form: " . $formId
            . ", itemName : '" . $fieldName . "', bfFlashUploadTicket: '" . $ticket
            . "', option: 'com_breezingformsng', format: 'html', flashUpload: 'true', Itemid: 0 },"
            . $newline . $fieldIndent . "url : '" . ($uploadUrl ?? $baseUrl . 'index.php') . "',"
            . $newline . $fieldIndent . "flash_swf_url : '" . $baseUrl
            . "components/com_breezingformsng/libraries/jquery/plupload/Moxie.swf',"
            . $newline . $fieldIndent . "filters : ["
            . $newline . $fieldIndent . '        {title : ' . $chooseFileLabel
            . ", extensions : '" . $extensions . "'}"
            . $newline . $fieldIndent . ']'
            . $newline . $indent . '});';
    }
}
