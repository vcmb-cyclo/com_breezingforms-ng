<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a static text/HTML element.
 */
final class ClassicStaticTextBuilder
{
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
