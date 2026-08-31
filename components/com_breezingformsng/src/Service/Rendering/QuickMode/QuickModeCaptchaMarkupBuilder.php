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

final class QuickModeCaptchaMarkupBuilder
{
    public function buildImage(
        string $attributes,
        string $elementId,
        string $class,
        string $src,
        string $suffix = ''
    ): string {
        $imageAttributes = trim($attributes);

        return '<img alt="" ' . ($imageAttributes !== '' ? $imageAttributes . ' ' : '')
            . 'id="' . $elementId . '" class="' . $class
            . '" src="' . $src . '"/>' . $suffix . "\n";
    }

    public function buildResponseInput(
        string $leadingAttributes,
        string $class,
        string $attributes = '',
        bool $preserveLeadingGap = false
    ): string {
        $leading = $leadingAttributes !== ''
            ? $leadingAttributes . ' '
            : ($preserveLeadingGap ? ' ' : '');

        return '<input ' . $leading . 'autocomplete="off" class="' . $class . '" '
            . $attributes . 'type="text" name="bfCaptchaEntry" id="bfCaptchaEntry" />' . "\n";
    }
}
