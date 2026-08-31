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

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeSelectBuilder
{
    public function build(
        string $class,
        string $fieldName,
        int $elementId,
        string $list,
        bool $multiple,
        string $attributes = '',
        string $style = '',
        bool $includeChosenAttribute = true
    ): string {
        $html = '<select ' . ($includeChosenAttribute ? 'data-chosen="no-chzn" ' : '')
            . 'class="' . $class . '" ' . $style
            . ($multiple ? 'multiple="multiple" ' : '') . $attributes
            . 'name="ff_nm_' . $fieldName . '[]" id="ff_elem' . $elementId . '">' . "\n";

        foreach (explode("\n", str_replace("\r", '', $list)) as $line) {
            $parts = explode(';', $line);

            if (count($parts) !== 3) {
                continue;
            }

            $html .= '<option ' . ($parts[0] == 1 ? 'selected="selected" ' : '')
                . 'value="' . htmlentities(trim($parts[2]), ENT_QUOTES, 'UTF-8') . '">'
                . htmlentities(trim($parts[1]), ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
        }

        return $html . '</select>' . "\n";
    }
}
