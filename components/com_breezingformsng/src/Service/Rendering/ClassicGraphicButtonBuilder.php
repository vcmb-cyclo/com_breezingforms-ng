<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a graphic button element.
 */
final class ClassicGraphicButtonBuilder
{
    public function build(
        int $elementId,
        string $name,
        string $source,
        string $label,
        string $wrapperStyle,
        string $wrapperClass,
        string $controlClass,
        int $width,
        int $height,
        bool $disabled,
        string $eventAttributes,
        int $layout,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $buttonAttributes = $disabled ? ' disabled="disabled"' : '';
        $buttonAttributes .= $eventAttributes;
        $dimensions = '';
        if ($width > 0) {
            $dimensions .= 'width="' . $width . '" ';
        }
        if ($height > 0) {
            $dimensions .= 'height="' . $height . '" ';
        }
        $image = '<img id="ff_img' . $elementId . '" src="' . $source . '" alt="' . $label . '" border="0" '
            . $dimensions . '/>';
        $emptyImage = '<img id="ff_img' . $elementId . '" src="' . $source . '" alt="" border="0" ' . $dimensions . '/>';

        $content = match ($layout) {
            1 => $indent . $indent . $indent . '<table cellpadding="0" cellspacing="6" border="0">' . $newline
                . $indent . $indent . $indent . $indent . '<tr><td nowrap style="text-align:center">' . $newline
                . $indent . $indent . $indent . $indent . $indent . $emptyImage . '<br/>' . $newline
                . $indent . $indent . $indent . $indent . $indent . $label . $newline
                . $indent . $indent . $indent . $indent . '</td></tr>' . $newline
                . $indent . $indent . $indent . '</table>' . $newline,
            2 => $indent . $indent . $indent . '<table cellpadding="0" cellspacing="6" border="0">' . $newline
                . $indent . $indent . $indent . $indent . '<tr><td nowrap style="text-align:center">' . $newline
                . $indent . $indent . $indent . $indent . $indent . $label . '<br/>' . $newline
                . $indent . $indent . $indent . $indent . $indent . $emptyImage . $newline
                . $indent . $indent . $indent . $indent . '</td></tr>' . $newline
                . $indent . $indent . $indent . '</table>.nlc()',
            3 => $indent . $indent . $indent . '<table cellpadding="0" cellspacing="6" border="0">' . $newline
                . $indent . $indent . $indent . $indent . '<tr>' . $newline
                . $indent . $indent . $indent . $indent . $indent . '<td>' . $label . '</td>' . $newline
                . $indent . $indent . $indent . $indent . $indent . '<td>' . $image . '</td>' . $newline
                . $indent . $indent . $indent . $indent . '</tr>' . $newline
                . $indent . $indent . $indent . '</table>' . $newline,
            default => $layout === 0
                ? $indent . $indent . $indent . '<table cellpadding="0" cellspacing="6" border="0">' . $newline
                    . $indent . $indent . $indent . $indent . '<tr><td>' . $newline
                    . $indent . $indent . $indent . $indent . $indent . $image . $newline
                    . $indent . $indent . $indent . $indent . '</td></tr>' . $newline
                    . $indent . $indent . $indent . '</table>' . $newline
                : $indent . $indent . $indent . '<table cellpadding="0" cellspacing="6" border="0">' . $newline
                    . $indent . $indent . $indent . $indent . '<tr>' . $newline
                    . $indent . $indent . $indent . $indent . $indent . '<td>' . $image . '</td>' . $newline
                    . $indent . $indent . $indent . $indent . $indent . '<td>' . $label . '</td>' . $newline
                    . $indent . $indent . $indent . $indent . '</tr>' . $newline
                    . $indent . $indent . $indent . '</table>' . $newline,
        };

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $wrapperStyle . '"' . $wrapperClass . '>' . $newline
            . $indent . $indent . '<button id="ff_elem' . $elementId . '" type="button" name="ff_nm_' . $name
            . '" value="' . $label . '"' . $buttonAttributes . $controlClass . '>' . $newline
            . $content . $indent . $indent . '</button>' . $newline
            . $indent . '</div>' . $newline;
    }
}
