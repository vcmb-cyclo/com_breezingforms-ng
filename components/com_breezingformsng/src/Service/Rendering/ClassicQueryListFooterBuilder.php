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
 * Builds the footer and pagination row of a classic Query List table.
 */
final class ClassicQueryListFooterBuilder
{
    public function build(
        int $rowId,
        int $span,
        int $pages,
        int $pageNavigation,
        string $rowClass,
        string $cellClass,
        string $pageStart,
        string $pagePrevious,
        string $pageNext,
        string $pageEnd,
        string $rowIndent = "\t",
        string $cellIndent = "\t\t",
        string $controlIndent = "\t\t\t",
        string $compactNewline = "\n",
        string $newline = "\n"
    ): string {
        $footer = $rowIndent . '<tr' . $rowClass . '>' . $compactNewline;
        $footer .= $cellIndent . '<td colspan="' . $span . '"' . $cellClass . '>' . $compactNewline;
        if ($pages > 1) {
            $footer .= $controlIndent;
            if ($pageNavigation <= 4) {
                $footer .= '&lt;&lt; ';
            }
            if ($pageNavigation <= 2) {
                $footer .= $pageStart . ' ';
            }
            if ($pageNavigation <= 4) {
                $footer .= '&lt; ';
            }
            if ($pageNavigation <= 2) {
                $footer .= $pagePrevious . ' ';
            }
            $footer .= $compactNewline;
            if ($pageNavigation % 2) {
                $footer .= $controlIndent . '1 ';
                for ($page = 2; $page <= $pages; $page++) {
                    $footer .= $compactNewline === ''
                        ? '<a href="javascript:ff_dispQueryPage(' . $rowId . ',' . $page . ');">' . $page . '</a> '
                        : '<a href="javascript:ff_dispQueryPage(' . $rowId . ',' . $page
                            . ');">' . $page . '</a> ' . $compactNewline;
                }
                $footer .= $compactNewline;
            }
            if ($pageNavigation <= 4) {
                $footer .= $controlIndent . '<a href="javascript:ff_dispQueryPage(' . $rowId . ',2);">';
                if ($pageNavigation <= 2) {
                    $footer .= $pageNext . ' ';
                }
                $footer .= '&gt;</a> ' . $compactNewline;
                $footer .= $controlIndent . '<a href="javascript:ff_dispQueryPage(' . $rowId . ',' . $pages . ');">';
                if ($pageNavigation <= 2) {
                    $footer .= $pageEnd . ' ';
                }
                $footer .= '&gt;&gt;</a>' . $compactNewline;
            }
        }
        $footer .= $cellIndent . '</td>' . $compactNewline;

        return $footer . $rowIndent . '</tr>' . $newline;
    }
}
