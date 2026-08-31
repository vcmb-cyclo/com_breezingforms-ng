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
 * Builds optional hidden fields used to preserve form display context.
 */
final class FormOptionalContextFieldsBuilder
{
    public function build(
        int $target,
        bool $inFrame,
        bool $border,
        int $page,
        int $align,
        int $top,
        string $indentation,
        bool $includeTarget = true,
        bool $includeFrame = true,
        bool $includeBorder = true,
        bool $includeLayoutFields = true,
        string $newline = "\r\n"
    ): string {
        $fields = [];

        if ($includeTarget && $target > 1) {
            $fields['ff_target'] = $target;
        }
        if ($includeFrame && $inFrame) {
            $fields['ff_frame'] = 1;
        }
        if ($includeBorder && $border) {
            $fields['ff_border'] = 1;
        }
        if ($page !== 1) {
            $fields['ff_page'] = $page;
        }
        if ($includeLayoutFields && $align !== 1) {
            $fields['ff_align'] = $align;
        }
        if ($includeLayoutFields && $top !== 0) {
            $fields['ff_top'] = $top;
        }

        $output = '';
        foreach ($fields as $name => $value) {
            $output .= $indentation . '<input type="hidden" name="' . $name . '" value="'
                . htmlentities((string) $value, ENT_QUOTES, 'UTF-8') . '"/>' . $newline;
        }

        return $output;
    }
}
