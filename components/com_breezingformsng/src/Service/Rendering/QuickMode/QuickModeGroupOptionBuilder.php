<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeGroupOptionBuilder
{
    public function build(
        string $type,
        string $class,
        string $fieldName,
        string $value,
        string $elementId,
        bool $checked,
        string $attributes = ''
    ): string {
        return '<input ' . ($checked ? 'checked="checked" ' : '') . ' class="' . $class . '" '
            . $attributes . 'type="' . $type . '" name="ff_nm_' . $fieldName . '[]" value="'
            . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $elementId . '"/>';
    }
}
