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
 * Builds the shared plain textarea control used by QuickMode renderers.
 */
final class QuickModeTextareaBuilder
{
    public function build(
        string $class,
        string $fieldName,
        string $value,
        int $elementId,
        string $attributes = '',
        string $placeholder = '',
        string $beforeClass = ''
    ): string {
        $placeholderAttribute = $placeholder !== ''
            ? 'placeholder="' . htmlentities($placeholder, ENT_QUOTES, 'UTF-8') . '" '
            : '';

        return '<textarea ' . $placeholderAttribute . $beforeClass
            . 'class="' . $class . '" ' . $attributes
            . 'name="ff_nm_' . $fieldName . '[]" id="ff_elem' . $elementId . '">'
            . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '</textarea>' . "\n";
    }
}
