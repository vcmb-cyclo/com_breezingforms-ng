<?php

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
