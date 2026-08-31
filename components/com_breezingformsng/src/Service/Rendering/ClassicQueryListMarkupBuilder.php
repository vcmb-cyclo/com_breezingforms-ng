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
 * Builds the classic Query List wrapper and table markup.
 */
final class ClassicQueryListMarkupBuilder
{
    public function open(
        int $rowId,
        string $style,
        string $wrapperClass,
        string $tableAttributes,
        string $tableClass,
        string $wrapperIndent = "\t",
        string $tableIndent = "\t\t",
        string $wrapperNewline = "\n",
        string $tableNewline = "\n"
    ): string {
        return $wrapperIndent . '<div id="ff_div' . $rowId . '" style="' . $style . '"' . $wrapperClass . '>'
            . $wrapperNewline
            . $tableIndent . '<table id="ff_elem' . $rowId . '"' . $tableAttributes . $tableClass . '>'
            . $tableNewline;
    }

    public function close(
        string $tableIndent = "\t\t",
        string $wrapperIndent = "\t",
        string $tableNewline = "\n",
        string $wrapperNewline = "\n"
    ): string {
        return $tableIndent . '</table>' . $tableNewline
            . $wrapperIndent . '</div>' . $wrapperNewline;
    }
}
