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
 * Builds the endpoints used by CAPTCHA validation scripts.
 */
final class CaptchaEndpointBuilder
{
    /**
     * @return array{captcha: string, image: string, check: string, recaptcha: string}
     */
    public function build(string $root, bool $administrator, int $form): array
    {
        $prefix = $root . ($administrator ? '/administrator' : '');

        return [
            'captcha' => $prefix . '/index.php?option=com_breezingformsng&bfCaptcha=1',
            'image' => $prefix . '/index.php?option=com_breezingformsng&bfCaptcha=1&bfMathRandom=',
            'check' => $prefix
                . '/index.php?raw=true&option=com_breezingformsng&checkCaptcha=true&Itemid=0&tmpl=component&value=',
            'recaptcha' => 'index.php?raw=true&option=com_breezingformsng&bfReCaptcha=true&form='
                . $form . '&Itemid=0&tmpl=component',
        ];
    }
}
