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

final class QuickModeCalendarButtonBuilder
{
    public function build(
        string $prefix,
        string $elementId,
        string $class,
        string $value,
        string $content,
        bool $idFirst = false
    ): string {
        $opening = $idFirst
            ? 'id="' . $elementId . '" ' . ($prefix !== '' ? $prefix . ' ' : '') . 'class="' . $class . '"'
            : ($prefix !== '' ? $prefix . ' ' : '') . 'id="' . $elementId . '" class="' . $class . '"';

        return '<button ' . $opening . ' value="'
            . htmlentities($value, ENT_QUOTES, 'UTF-8') . '">' . $content . '</button>' . "\n";
    }
}
