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
 * Builds the classic renderer markup for a file upload element.
 */
final class ClassicFileUploadBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        int $size,
        int $maxLength,
        bool $disabled,
        string $accept,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = '';
        if ($size > 0) {
            $attributes .= ' size="' . $size . '"';
        }
        if ($maxLength > 0) {
            $attributes .= ' maxlength="' . $maxLength . '"';
        }
        if ($disabled) {
            $attributes .= ' disabled="disabled"';
        }
        if ($accept !== '') {
            $attributes .= ' accept="' . $accept . '"';
        }
        $attributes .= $eventAttributes;

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<input id="ff_elem' . $elementId . '"' . $attributes . ' type="file" name="ff_nm_' . $name
            . '[]"' . $controlClass . '/>' . $newline . $indent . '</div>' . $newline;
    }
}
