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
 * Builds the classic renderer markup for a hidden input element.
 */
final class ClassicHiddenInputBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $value,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        return $indent . '<input id="ff_elem' . $elementId . '" type="hidden" name="ff_nm_' . $name
            . '[]" value="' . $value . '" />' . $newline;
    }
}
