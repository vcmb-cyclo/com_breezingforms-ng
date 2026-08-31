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

final class QuickModeCaptchaReloadScriptBuilder
{
    public function build(string $captchaUrl): string
    {
        return "document.getElementById('bfCaptchaEntry').value='';"
            . "document.getElementById('bfCaptchaEntry').focus();"
            . "document.getElementById('ff_capimgValue').src = '" . $captchaUrl
            . "&bfMathRandom=' + Math.random(); return false";
    }
}
