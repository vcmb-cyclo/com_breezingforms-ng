<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a regular button element.
 */
final class ClassicRegularButtonBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $label,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        bool $disabled,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = $disabled ? ' disabled="disabled"' : '';
        $attributes .= $eventAttributes;

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<input id="ff_elem' . $elementId . '" type="button" name="ff_nm_' . $name
            . '" value="' . $label . '"' . $attributes . $controlClass . '/>' . $newline
            . $indent . '</div>' . $newline;
    }
}
