<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/**
 * Builds the shared plain textarea control used by QuickMode renderers.
 */
final class QuickModeTextareaBuilder
{
    public function build(
        string $class,
        string $fieldName,
        string $value,
        int $elementId,
        string $attributes = '',
        string $placeholder = '',
        string $beforeClass = ''
    ): string {
        $placeholderAttribute = $placeholder !== ''
            ? 'placeholder="' . htmlentities($placeholder, ENT_QUOTES, 'UTF-8') . '" '
            : '';

        return '<textarea ' . $placeholderAttribute . $beforeClass
            . 'class="' . $class . '" ' . $attributes
            . 'name="ff_nm_' . $fieldName . '[]" id="ff_elem' . $elementId . '">'
            . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '</textarea>' . "\n";
    }
}
