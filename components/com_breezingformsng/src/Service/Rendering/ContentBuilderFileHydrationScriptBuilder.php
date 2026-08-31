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
 * Builds the script that restores existing ContentBuilder upload controls.
 */
final class ContentBuilderFileHydrationScriptBuilder
{
    public function build(int $elementId, string $controlsHtml): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- Preserve legacy JavaScript lines verbatim.
        return '
                                                    if (document.createTextNode){
                                                        if(!document.getElementById("bfFlashFileQueue' . $elementId . '")){
                                                           var mydiv = document.createElement("div");
                                                           mydiv.innerHTML = "<br/>' . $controlsHtml . '";
                                                           JQuery("#ff_elem' . $elementId . '_files").append(mydiv);
                                                        } else {
                                                           var mydiv = document.createElement("div");
                                                           mydiv.innerHTML = "' . $controlsHtml . '";
                                                           mydiv.innerHTML = "<br/>" + mydiv.innerHTML;
                                                           JQuery("#bfFlashFileQueue' . $elementId . '").after(mydiv);
                                                        }
                                                    }' . "\n";
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
