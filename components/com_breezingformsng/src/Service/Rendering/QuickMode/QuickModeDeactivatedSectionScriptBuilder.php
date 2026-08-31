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

/** Builds the script registering a disabled QuickMode section. */
final class QuickModeDeactivatedSectionScriptBuilder
{
    public function build(string $name, string $newline = "\n"): string
    {
        return '<script type="text/javascript">bfRegisterDeactivatedSection('
            . json_encode($name)
            . ');</script>' . $newline;
    }
}
