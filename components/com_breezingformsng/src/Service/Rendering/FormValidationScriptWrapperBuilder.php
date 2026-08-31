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
 * Wraps the form-level validation JavaScript in its historical script tag.
 */
final class FormValidationScriptWrapperBuilder
{
    public function open(string $fileExtensionsCheck, string $captchaFunction, string $newline = "\n"): string
    {
        return '<script type="text/javascript">' . $newline
            . '<!--' . $newline
            . $newline
            . $fileExtensionsCheck
            . $captchaFunction;
    }

    public function close(string $newline = "\n"): string
    {
        return '//-->' . $newline . '</script>' . $newline;
    }
}
