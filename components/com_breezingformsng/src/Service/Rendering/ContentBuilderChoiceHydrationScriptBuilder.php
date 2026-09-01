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
 * Builds ContentBuilder hydration scripts for checkbox and radio controls.
 */
final class ContentBuilderChoiceHydrationScriptBuilder
{
    public function build(string $controlType, string $recordName, int $formId, string $value): string
    {
        $script = '';

        foreach (explode(',', $value) as $choice) {
            $choice = trim($choice);
            $encodedChoice = json_encode($choice);
            // phpcs:disable Generic.Files.LineLength.TooLong -- Preserve legacy JavaScript lines verbatim.
            $script .= '
                                                for(var i = 0;i < document.ff_form' . $formId . '.elements.length;i++){
                                                        if(document.ff_form' . $formId . '.elements[i].type == "' . $controlType . '" && document.ff_form' . $formId . '.elements[i].name == "ff_nm_' . $recordName . '[]" && document.ff_form' . $formId . '.elements[i].value == ' . $encodedChoice . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.ff_form' . $formId . '.elements[i]).attr("checked")){
                                                                    JQuery(document.ff_form' . $formId . '.elements[i]).click();
                                                                }
                                                        }
                                                }' . "\n";
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        return $script;
    }
}
