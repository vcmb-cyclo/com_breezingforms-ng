<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeCheckboxBuilder
{
    public function build(
        string $class,
        string $fieldName,
        string $value,
        int $elementId,
        bool $checked,
        string $attributes = ''
    ): string {
        return '<input class="' . $class . '" '
            . ($checked ? 'checked="checked" ' : '')
            . $attributes
            . 'type="checkbox" name="ff_nm_' . $fieldName . '[]" value="'
            . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $elementId . '"/>' . "\n";
    }
}
