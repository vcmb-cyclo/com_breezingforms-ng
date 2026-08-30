<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a text input element.
 */
final class ClassicTextInputBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $value,
        string $wrapperStyle,
        string $wrapperClass,
        string $inputClass,
        int $width,
        int $widthMode,
        int $maxLength,
        bool $password,
        int $state,
        string $eventAttributes,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $attributes = '';
        if ($width > 0) {
            $attributes .= $widthMode > 0
                ? ' style="width:' . $width . 'px;"'
                : ' size="' . $width . '"';
        }
        if ($maxLength > 0) {
            $attributes .= ' maxlength="' . $maxLength . '"';
        }
        $attributes .= $password ? ' type="password"' : ' type="text"';
        $attributes .= match ($state) {
            1 => ' disabled="disabled"',
            2 => ' readonly="readonly"',
            default => '',
        };
        $attributes .= $eventAttributes;

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<input id="ff_elem' . $elementId . '"' . $attributes . ' name="ff_nm_' . $name
            . '[]" value="' . $value . '"' . $inputClass . '/>' . $newline
            . $indent . '</div>' . $newline;
    }
}
