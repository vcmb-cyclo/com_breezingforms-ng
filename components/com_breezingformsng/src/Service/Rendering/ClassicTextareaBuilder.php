<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a textarea element.
 */
final class ClassicTextareaBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $value,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        int $width,
        int $widthMode,
        int $height,
        int $heightMode,
        bool $mozilla,
        int $state,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = match ($state) {
            1 => ' disabled="disabled"',
            2 => ' readonly="readonly"',
            default => '',
        };
        $styles = '';
        if ($width > 0) {
            if ($widthMode > 0) {
                $styles .= 'width:' . $width . 'px;';
            } else {
                $attributes .= ' cols="' . $width . '"';
            }
        }
        if ($height > 0) {
            if ($heightMode > 0) {
                $styles .= 'height:' . $height . 'px;';
            } else {
                $rows = $height;
                if ($rows > 1 && $mozilla) {
                    $rows--;
                }
                $attributes .= ' rows="' . $rows . '"';
            }
        }
        if ($styles !== '') {
            $attributes .= ' style="' . $styles . '"';
        }
        $attributes .= $eventAttributes;

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<textarea id="ff_elem' . $elementId . '" name="ff_nm_' . $name . '[]"' . $attributes
            . $controlClass . '>' . $value . '</textarea>' . $newline
            . $indent . '</div>' . $newline;
    }
}
