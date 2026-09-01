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
 * Builds the QuickMode form opening tag.
 */
final class QuickModeFormTagBuilder
{
    public function build(
        string $action,
        string $formId,
        string $customClass = '',
        string $newline = "\n"
    ): string {
        $params = ' action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '"'
            . ' method="post"'
            . ' name="' . htmlspecialchars($formId, ENT_QUOTES, 'UTF-8') . '"'
            . ' id="' . htmlspecialchars($formId, ENT_QUOTES, 'UTF-8') . '"'
            . ' enctype="multipart/form-data"';
        if ($customClass !== '') {
            $params .= ' class="' . htmlspecialchars($customClass, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<form data-ajax="false"' . $params
            . ' accept-charset="utf-8" onsubmit="return false;" class="bfQuickMode">'
            . $newline;
    }
}
