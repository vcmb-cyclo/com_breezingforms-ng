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
 * Builds the classic renderer markup for a select list element.
 */
final class ClassicSelectBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        string $size,
        string $optionsText,
        int $width,
        int $height,
        bool $multiple,
        bool $disabled,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = '';
        $styles = '';
        if ($width > 0) {
            $styles .= 'width:' . $width . 'px;';
        }
        if ($height > 0) {
            $styles .= 'height:' . $height . 'px;';
        }
        if ($multiple) {
            $attributes .= ' multiple="multiple"';
        }
        if ($disabled) {
            $attributes .= ' disabled="disabled"';
        }
        $attributes .= $eventAttributes;
        if ($size !== '') {
            $attributes .= ' size="' . $size . '"';
        }
        if ($styles !== '') {
            $attributes .= ' style="' . $styles . '"';
        }

        $options = '';
        $lines = explode('\n', (string) preg_replace('/([\\r\\n])/s', '\n', $optionsText));
        foreach ($lines as $line) {
            $parts = explode(';', $line);
            $selected = '';
            $value = '';
            $text = '';
            switch (count($parts)) {
                case 1:
                    if (trim($parts[0]) !== '') {
                        $selected = '0';
                        $value = $text = $parts[0];
                    }
                    break;
                case 2:
                    $selected = $parts[0];
                    $value = $text = $parts[1];
                    break;
                default:
                    $selected = $parts[0];
                    $text = $parts[1];
                    $value = $parts[2];
                    break;
            }
            if (trim($selected) === '') {
                continue;
            }

            $optionAttributes = '';
            if (trim($value) !== '') {
                if ($value === '""' || $value === "''") {
                    $value = '';
                }
                $optionAttributes .= ' value="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
            if ($selected == 1) {
                $optionAttributes .= ' selected="selected"';
            }
            $options .= $indent . $indent . $indent . '<option' . $optionAttributes . '>'
                . htmlspecialchars(trim($text), ENT_QUOTES) . '</option>' . $newline;
        }

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<select id="ff_elem' . $elementId . '" name="ff_nm_' . $name . '[]" ' . $attributes
            . $controlClass . '>' . $newline . $options . $indent . $indent . '</select>' . $newline
            . $indent . '</div>' . $newline;
    }
}
