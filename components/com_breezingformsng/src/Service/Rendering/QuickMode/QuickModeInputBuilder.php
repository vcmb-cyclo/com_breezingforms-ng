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

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/**
 * Builds the shared input control markup used by QuickMode renderers.
 */
final class QuickModeInputBuilder
{
    public function build(
        string $class,
        string $type,
        string $fieldName,
        string $value,
        int $elementId,
        string $attributes = '',
        string $placeholder = '',
        string $suffix = ''
    ): string {
        $escapedClass = htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedFieldName = htmlspecialchars($fieldName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $placeholderAttribute = $placeholder !== ''
            ? 'placeholder="' . htmlentities($placeholder, ENT_QUOTES, 'UTF-8') . '" '
            : '';

        return '<input ' . $placeholderAttribute
            . 'class="' . $escapedClass . '" ' . $attributes
            . 'type="' . $escapedType . '" name="ff_nm_' . $escapedFieldName . '[]"'
            . ' value="' . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '"'
            . ' id="ff_elem' . $elementId . '"' . $suffix . '/>' . "\n";
    }
}
