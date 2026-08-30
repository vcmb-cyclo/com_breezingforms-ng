<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds one cell of a classic Query List data row.
 */
final class ClassicQueryListCellBuilder
{
    /**
     * @param callable(string): string $classResolver
     */
    public function build(
        object $column,
        string|int|float|null $value,
        int $columnIndex,
        int $rowIndex,
        int $rowId,
        string $rowName,
        int $selectionMode,
        bool $firstDataRow,
        int &$skip,
        callable $classResolver,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        if ($column->thspan <= 0) {
            return '';
        }

        $attributes = '';
        $style = match ((int) $column->align) {
            1 => 'text-align:left;',
            2 => 'text-align:center;',
            3 => 'text-align:right;',
            4 => 'text-align:justify;',
            default => '',
        };
        $attributes .= match ((int) $column->valign) {
            1 => ' valign="top"',
            2 => ' valign="middle"',
            3 => ' valign="bottom"',
            4 => ' valign="baseline"',
            default => '',
        };
        if ($column->wrap == 1) {
            $attributes .= ' nowrap="nowrap"';
        }
        $className = $firstDataRow ? $column->class2 : $column->class3;
        if ($className !== '') {
            $attributes .= ' class="' . $classResolver((string) $className) . '"';
        }
        if ($skip === 0 && $column->thspan > 1) {
            $skip = $column->thspan - 1;
        }
        if ($skip > 0 && (int) $column->width > 0) {
            $style .= 'width:' . $column->width . ($column->widthmd ? '%;' : 'px;');
        }
        if ($skip > 0) {
            $skip--;
        }
        if ($style !== '') {
            $attributes .= ' style="' . $style . '"';
        }

        if ($columnIndex === 0 && $selectionMode > 0) {
            $controlType = $selectionMode === 1 ? 'checkbox' : 'radio';
            $cell = '<td' . $attributes . '><input type="' . $controlType . '" id="ff_cb' . $rowId . '_' . $rowIndex
                . '" value="' . $value . '"  name="ff_nm_' . $rowName . '[]"/></td>';
        } else {
            $cell = '<td' . $attributes . '>' . $value . '</td>';
        }

        return $indent . $cell . $newline;
    }
}
