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
 * Builds the script that hydrates a simple ContentBuilder control.
 */
final class ContentBuilderValueHydrationScriptBuilder
{
    public function build(string $type, string $recordName, int $elementId, mixed $value): string
    {
        $script = $type === 'Calendar' ? 'setTimeout(function(){' : '';
        $encodedValue = json_encode($value);

        $script .= 'if(typeof JQuery != "undefined"){';
        $script .= 'JQuery("[name=\"ff_nm_' . $recordName . '[]\"]").val(' . $encodedValue
            . ');if(typeof JQuery != "undefined")JQuery("[name=\"ff_nm_' . $recordName
            . '[]\"]").trigger("change");';
        $script .= '}else{if(document.getElementById("ff_elem' . $elementId . '"))'
            . 'document.getElementById("ff_elem' . $elementId . '").value=' . $encodedValue
            . ';if(typeof JQuery != "undefined")JQuery(document.getElementById("ff_elem'
            . $elementId . '")).trigger("change");}' . "\n";

        if ($type === 'Calendar') {
            $script .= '}, 100);';
        }

        return $script;
    }
}
