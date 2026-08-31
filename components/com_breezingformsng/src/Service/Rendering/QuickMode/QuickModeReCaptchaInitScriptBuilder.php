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

/** Builds the shared visible ReCaptcha initialization script. */
final class QuickModeReCaptchaInitScriptBuilder
{
    public function visibleApiUrl(string $languageTag): string
    {
        $language = explode('-', $languageTag)[0];

        return 'https://www.google.com/recaptcha/api.js?hl=' . $language
            . '&onload=onloadBFNewRecaptchaCallback&render=explicit';
    }

    public function invisibleApiUrl(): string
    {
        return 'https://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit';
    }

    /** @param array<string, mixed> $configuration */
    public function visible(array $configuration): string
    {
        return '<script data-usercentrics="reCAPTCHA" type="text/javascript">bfInitVisibleReCaptcha('
            . json_encode($configuration)
            . ');</script>';
    }

    /** @param array<string, mixed> $configuration */
    public function encode(array $configuration): string
    {
        return (string) json_encode($configuration);
    }
}
