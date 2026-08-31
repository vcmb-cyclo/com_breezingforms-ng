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
 * Builds the classic renderer markup for a regular button element.
 */
final class ClassicRegularButtonBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $label,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        bool $disabled,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = $disabled ? ' disabled="disabled"' : '';
        $attributes .= $eventAttributes;

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<input id="ff_elem' . $elementId . '" type="button" name="ff_nm_' . $name
            . '" value="' . $label . '"' . $attributes . $controlClass . '/>' . $newline
            . $indent . '</div>' . $newline;
    }
}
