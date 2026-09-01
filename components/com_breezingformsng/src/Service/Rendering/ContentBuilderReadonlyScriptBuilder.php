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
 * Builds the JavaScript that deactivates non-editable ContentBuilder fields.
 */
final class ContentBuilderReadonlyScriptBuilder
{
    /**
     * Build the function body used to deactivate the supplied field IDs.
     *
     * @param list<int|string> $fieldIds
     */
    public function build(array $fieldIds): string
    {
        if ($fieldIds === []) {
            return '';
        }

        $script = 'function bfContentBuilderFieldHasVisibleControl(fieldId){' . "\n";
        $script .= 'var wrap = JQuery("#bfElemWrap" + fieldId);' . "\n";
        $script .= 'if(!wrap.length){ return false; }' . "\n";
        $script .= 'var hasVisibleControl = false;' . "\n";
        $script .= 'wrap.find(".ff_elem").each(function(){' . "\n";
        $script .= 'if(typeof this.type != "undefined" && this.type != "hidden"){';
        $script .= ' hasVisibleControl = true; return false; }' . "\n";
        $script .= '});' . "\n";
        $script .= 'return hasVisibleControl;' . "\n";
        $script .= '}' . "\n";
        $script .= 'function bfDisableContentBuilderFields(){' . "\n";

        foreach ($fieldIds as $fieldId) {
            $fieldId = (string) $fieldId;
            $element = 'document.getElementById("ff_elem' . $fieldId . '")';
            $visibleControl = 'bfContentBuilderFieldHasVisibleControl("' . $fieldId . '")';
            $hideControl = 'if(typeof JQuery != "undefined" && !' . $visibleControl . '){';
            $hideControl .= ' JQuery("#bfElemWrap' . $fieldId . '").css("display", "none"); }';
            $script .= 'if(typeof ' . $element . '.disabled != "undefined"){' . "\n";
            $script .= 'bfCbMainElement = ' . $element . ';' . "\n";
            $script .= 'bfCbRespectReadonly = (bfCbMainElement && ';
            $script .= 'typeof bfCbMainElement.readOnly != "undefined" && ';
            $script .= 'bfCbMainElement.readOnly);' . "\n";
            $script .= 'bfCbName = ' . $element . '.name;' . "\n";
            $script .= 'if(typeof document.getElementsByName != "undefined"){' . "\n";
            $script .= 'bfCbElements = document.getElementsByName(bfCbName);' . "\n";
            $script .= 'for(var i = 0; i < bfCbElements.length; i++){' . "\n";
            $script .= 'if(typeof bfCbElements[i].disabled != "undefined" && !bfCbRespectReadonly){' . "\n";
            $script .= 'bfCbElements[i].disabled = true;' . "\n";
            $script .= '}' . "\n";
            $script .= 'bfDeactivateField[bfCbName]=true;' . "\n";
            $script .= $hideControl . "\n";
            $script .= '}' . "\n";
            $script .= '}else{' . "\n";
            $script .= 'if(!bfCbRespectReadonly){ ' . $element . '.disabled = true; }' . "\n";
            $script .= 'bfDeactivateField[bfCbName]=true;' . "\n";
            $script .= $hideControl . "\n";
            $script .= '}' . "\n";
            $script .= '}' . "\n";
        }

        return $script . '}' . "\n";
    }
}
