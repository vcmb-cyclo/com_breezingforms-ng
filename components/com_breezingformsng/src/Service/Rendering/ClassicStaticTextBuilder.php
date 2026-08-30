<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a static text/HTML element.
 */
final class ClassicStaticTextBuilder
{
    public function buildImage(
        int $elementId,
        string $style,
        string $classAttribute,
        string $imageClassAttribute,
        string $source,
        string $alternative,
        int $width,
        int $height,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $dimensions = '';
        if ($width > 0) {
            $dimensions .= 'width="' . $width . '" ';
        }
        if ($height > 0) {
            $dimensions .= 'height="' . $height . '" ';
        }

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $style . '"' . $classAttribute . '>' . $newline
            . $indent . $indent . '<img id="ff_elem' . $elementId . '" src="' . $source . '"  alt="' . $alternative
            . '" border="0" ' . $dimensions . $imageClassAttribute . '/>' . $newline
            . $indent . '</div>' . $newline;
    }

    public function buildRectangle(
        int $elementId,
        string $style,
        string $classAttribute,
        string $border,
        string $backgroundColor,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        if ($border !== '') {
            $style .= 'border:' . $border . ';';
        }

        if ($backgroundColor !== '') {
            $style .= 'background-color:' . $backgroundColor . ';';
        }

        return $indent . '<div id="ff_div' . $elementId . '" style="font-size:0px;' . $style . '"'
            . $classAttribute . '></div>' . $newline;
    }

    public function build(
        int $elementId,
        string $style,
        string $classAttribute,
        string $content,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        return $indent . '<div id="ff_div' . $elementId . '" style="' . $style . '"' . $classAttribute . '>'
            . $content . '</div>' . $newline;
    }
}
