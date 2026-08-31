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
 * Builds the existing-file controls for a ContentBuilder file upload.
 */
final class ContentBuilderFileUploadScriptBuilder
{
    /**
     * @param list<string> $displayNames
     * @return array{count: int, html: string, deactivation: string}
     */
    public function build(int $elementId, string $recordName, int $fileCount, array $displayNames): array
    {
        $html = '';
        foreach ($displayNames as $index => $displayName) {
            $html .= '<div><input type=\"checkbox\" onchange=\"bfCheckUploadValidation(\'ff_elem'
                . $elementId . '\', this, \'ff_nm_' . $recordName . '[]\')\" value=\"1\" name=\"cb_delete_'
                . $elementId . '[' . $index . ']\" id=\"cb_delete_' . $elementId . '_' . $index
                . '\"/> <label style=\"margin-left: 5px; float: none !important; display: inline !important;\" '
                . 'for=\"cb_delete_'
                . $elementId . '_' . $index . '\">' . $displayName . '</label></div>';
        }

        return [
            'count' => $fileCount,
            'html' => $html,
            'deactivation' => $displayNames === []
                ? ''
                : 'bfDeactivateField["ff_nm_' . $recordName . '[]"]=true;' . "\n",
        ];
    }
}
