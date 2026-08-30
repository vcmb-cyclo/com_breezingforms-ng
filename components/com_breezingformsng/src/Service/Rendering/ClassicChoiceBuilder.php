<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a checkbox or radio button.
 */
final class ClassicChoiceBuilder
{
    public function build(
        string $type,
        int $elementId,
        string $name,
        string $value,
        string $label,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        bool $checked,
        bool $disabled,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = $checked ? ' checked="checked"' : '';
        if ($disabled) {
            $attributes .= ' disabled="disabled"';
        }
        $attributes .= $eventAttributes;

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<input id="ff_elem' . $elementId . '" type="' . $type . '" name="ff_nm_' . $name
            . '[]" value="' . $value . '"' . $attributes . $controlClass . '/><label id="ff_lbl' . $elementId . '" for="ff_elem'
            . $elementId . '"> ' . $label . '</label>' . $newline
            . $indent . '</div>' . $newline;
    }
}
