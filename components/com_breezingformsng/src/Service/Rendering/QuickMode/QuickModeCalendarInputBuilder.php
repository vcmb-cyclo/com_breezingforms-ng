<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeCalendarInputBuilder
{
    public function build(
        string $class,
        string $fieldName,
        int $elementId,
        string $value,
        string $attributes = ''
    ): string {
        return '<input autocomplete="off" class="' . $class . '" ' . $attributes
            . 'type="text" name="ff_nm_' . $fieldName . '[]"  id="ff_elem' . $elementId
            . '" value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '"/>' . "\n";
    }
}
