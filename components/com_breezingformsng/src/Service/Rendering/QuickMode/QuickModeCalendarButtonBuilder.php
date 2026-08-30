<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeCalendarButtonBuilder
{
    public function build(
        string $prefix,
        string $elementId,
        string $class,
        string $value,
        string $content,
        bool $idFirst = false
    ): string {
        $opening = $idFirst
            ? 'id="' . $elementId . '" ' . ($prefix !== '' ? $prefix . ' ' : '') . 'class="' . $class . '"'
            : ($prefix !== '' ? $prefix . ' ' : '') . 'id="' . $elementId . '" class="' . $class . '"';

        return '<button ' . $opening . ' value="'
            . htmlentities($value, ENT_QUOTES, 'UTF-8') . '">' . $content . '</button>' . "\n";
    }
}
