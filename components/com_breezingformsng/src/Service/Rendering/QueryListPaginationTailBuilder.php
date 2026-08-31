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

/** Builds the optional final statements of the Query List page callback. */
final class QueryListPaginationTailBuilder
{
    public function build(bool $hasCheckboxes, int $heightMode, int $height, bool $inFrame, string $newline): string
    {
        $code = '';

        if ($hasCheckboxes) {
            $code .= '    if (checkbox) ff_selectAllQueryRows(id, false);' . $newline;
        }
        if ($heightMode > 0) {
            $code .= '    ff_resizepage(' . $heightMode . ', ' . $height . ');' . $newline;
        }
        if ($inFrame) {
            $code .= '    parent.window.scrollTo(0,0);' . $newline;
        }

        return $code . '    window.scrollTo(0,0);' . $newline;
    }
}
