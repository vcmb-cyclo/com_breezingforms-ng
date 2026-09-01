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

/** Builds the shared registration script for HTML editor textareas. */
final class QuickModeHtmlTextareaScriptBuilder
{
    public function build(string $name, string $contentExpression, string $newline = ''): string
    {
        return '<script type="text/javascript">bfRegisterHtmlTextarea('
            . json_encode($name) . ', function () { return '
            . $contentExpression . '; });</script>' . $newline;
    }
}
