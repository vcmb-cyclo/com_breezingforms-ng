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
 * Wraps the ContentBuilder readonly-field script in its historical tag.
 */
final class ContentBuilderReadonlyScriptWrapperBuilder
{
    public function build(string $script, string $newline = "\n"): string
    {
        return '<script type="text/javascript">' . $newline
            . '<!--' . $newline
            . $script
            . '//-->' . $newline
            . '</script>' . $newline;
    }
}
