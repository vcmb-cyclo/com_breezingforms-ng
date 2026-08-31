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
 * Builds the JavaScript expression used to read a Joomla editor value.
 */
final class QuickModeEditorValueBuilder
{
    public static function build(mixed $editor): string
    {
        return 'Joomla.editors.instances[' . json_encode($editor) . '].getValue()';
    }
}
