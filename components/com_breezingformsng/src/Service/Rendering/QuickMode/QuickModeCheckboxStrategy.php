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
 * Builds the shared checkbox control while renderers retain their wrappers.
 */
final class QuickModeCheckboxStrategy
{
    /**
     * @param array<string, mixed> $field
     */
    public function build(
        array $field,
        string $class,
        string $attributes,
        bool $readonly = false
    ): string {
        return (new QuickModeCheckboxBuilder())->build(
            $class,
            (string) $field['bfName'],
            (string) ($field['value'] ?? ''),
            (int) $field['dbId'],
            (bool) ($field['checked'] ?? false),
            $attributes . ($readonly ? ' disabled="disabled" ' : '')
        );
    }
}
