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
 * Builds ContentBuilder hydration scripts for select lists.
 */
final class ContentBuilderSelectHydrationScriptBuilder
{
    public function build(int $elementId, string $value): string
    {
        $script = '';

        foreach (explode(',', $value) as $choice) {
            $choice = trim($choice);
            $encodedChoice = json_encode($choice);
            // phpcs:disable Generic.Files.LineLength.TooLong -- Preserve legacy JavaScript lines verbatim.
            $script .= 'for(var i = 0; i < document.getElementById("ff_elem' . $elementId . '").options.length; i++){
                                                        if(document.getElementById("ff_elem' . $elementId . '").options[i].value == ' . $encodedChoice . '){
                                                                if(typeof JQuery != "undefined" && !JQuery(document.getElementById("ff_elem' . $elementId . '").options[i]).attr("selected")){
                                                                    JQuery(document.getElementById("ff_elem' . $elementId . '").options[i]).attr("selected", true).trigger("change");
                                                                }
                                                        }
                                                }' . "\n";
            // phpcs:enable Generic.Files.LineLength.TooLong
        }

        return $script;
    }
}
