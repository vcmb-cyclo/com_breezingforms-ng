<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeSubmitButtonBuilder
{
    public function build(
        string $tag,
        string $prefix,
        string $attributes,
        string $type,
        string $fieldName,
        int $elementId,
        string $content = '',
        string $afterType = '',
        string $afterId = ''
    ): string {
        $html = '<' . $tag . ' ' . $prefix . ' ' . $attributes . 'type="' . $type . '"' . $afterType
            . ' name="ff_nm_' . $fieldName . '[]" id="ff_elem' . $elementId . '"' . $afterId;

        return $tag === 'button'
            ? $html . '>' . $content . '</button>' . "\n"
            : $html . '/>' . "\n";
    }
}
