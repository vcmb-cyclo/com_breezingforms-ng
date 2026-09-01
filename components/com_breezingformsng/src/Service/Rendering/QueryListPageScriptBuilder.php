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

/** Assembles the complete client-side Query List page callback. */
final class QueryListPageScriptBuilder
{
    public function __construct(
        private readonly QueryListRowsRefreshBuilder $rowsRefreshBuilder,
        private readonly QueryListNavigationBuilder $navigationBuilder,
        private readonly QueryListPaginationTailBuilder $paginationTailBuilder
    ) {
    }

    /**
     * @param array{start: string, previous: string, next: string, end: string} $labels
     */
    public function build(
        array $labels,
        bool $hasCheckboxes,
        int $heightMode,
        int $height,
        bool $inFrame,
        string $newline
    ): string {
        return 'function ff_dispQueryPage(id,page)' . $newline .
            '{' . $newline .
            '    var forced = false;' . $newline .
            '    if (arguments.length>2) forced = arguments[2];' . $newline .
            $this->rowsRefreshBuilder->build($newline) .
            '    if (pagenav > 0 && pagesize > 0) {' . $newline .
            '        var navi = \'\';' . $newline .
            $this->navigationBuilder->build($labels, $newline) . $newline .
            '        rows[header+pagesize].cells[0].innerHTML = navi;' . $newline .
            '    } // if' . $newline .
            '    ff_queryCurrPage[id] = page;' . $newline .
            $this->paginationTailBuilder->build($hasCheckboxes, $heightMode, $height, $inFrame, $newline) .
            '} // ff_dispQueryPage';
    }
}
