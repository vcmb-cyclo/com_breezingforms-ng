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

/** Builds the inline error-handling configuration shared by Bootstrap renderers. */
final class QuickModeErrorRuntimeConfigBuilder
{
    public static function build(bool $showDefaultErrors, bool $pageScoped, string $newline): string
    {
        return 'var bfUseErrorAlerts = false;' . $newline
            . 'var bfShowDefaultErrors = ' . ($showDefaultErrors ? 'true' : 'false') . ';' . $newline
            . 'var bfErrorPageScoped = ' . ($pageScoped ? 'true' : 'false') . ';' . $newline;
    }
}
