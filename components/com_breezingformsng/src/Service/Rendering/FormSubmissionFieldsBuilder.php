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
 * Builds the mode-specific hidden fields required to submit a form.
 */
final class FormSubmissionFieldsBuilder
{
    public function build(
        int $formId,
        string $indent,
        string $newline,
        bool $backend = false,
        bool $frame = false
    ): string {
        $fields = '';
        if ($backend || $frame) {
            $fields .= $indent . '<input type="hidden" name="option" value="com_breezingformsng"/>' . $newline;
            $fields .= $indent . '<input type="hidden" name="' . ($backend ? 'act' : 'ff_frame') . '" value="' . ($backend ? 'run' : '1') . '"/>' . $newline;
        }

        $fields .= $indent . '<input type="hidden" name="ff_form" value="'
            . htmlentities((string) $formId, ENT_QUOTES, 'UTF-8') . '"/>' . $newline;
        $fields .= $indent . '<input type="hidden" name="ff_task" value="submit"/>' . $newline;

        return $fields;
    }
}
