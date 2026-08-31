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

final class QuickModeGroupOptionBuilder
{
    public function build(
        string $type,
        string $class,
        string $fieldName,
        string $value,
        string $elementId,
        bool $checked,
        string $attributes = ''
    ): string {
        return '<input ' . ($checked ? 'checked="checked" ' : '') . ' class="'
            . htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" '
            . $attributes . 'type="' . htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" name="ff_nm_' . htmlspecialchars($fieldName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '[]" value="'
            . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '" id="ff_elem'
            . htmlspecialchars($elementId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"/>';
    }
}
