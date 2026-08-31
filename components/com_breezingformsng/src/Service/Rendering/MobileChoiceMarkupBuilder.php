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
 * Builds the fallback markup offering the mobile form version.
 */
final class MobileChoiceMarkupBuilder
{
    public function build(string $mobileUrl, string $label, string $newline = "\n"): string
    {
        return '<script type="text/javascript">' . $newline
            . '                <!--' . $newline
            . '                var bf_mobile_url = ' . json_encode($mobileUrl) . ';' . $newline
            . '                //-->' . $newline
            . '                </script>' . $newline
            . '<div style="display: block; text-align: center;"><button class="ff_elem btn btn-primary" onclick="location.href=bf_mobile_url;"><span>'
            . $label . '</span></button></div><div></div>';
    }
}
