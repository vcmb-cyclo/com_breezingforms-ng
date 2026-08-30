<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/**
 * Builds the shared input control markup used by QuickMode renderers.
 */
final class QuickModeInputBuilder
{
    public function build(
        string $class,
        string $type,
        string $fieldName,
        string $value,
        int $elementId,
        string $attributes = '',
        string $placeholder = '',
        string $suffix = ''
    ): string {
        $placeholderAttribute = $placeholder !== ''
            ? 'placeholder="' . htmlentities($placeholder, ENT_QUOTES, 'UTF-8') . '" '
            : '';

        return '<input ' . $placeholderAttribute
            . 'class="' . $class . '" ' . $attributes
            . 'type="' . $type . '" name="ff_nm_' . $fieldName . '[]"'
            . ' value="' . htmlentities(trim($value), ENT_QUOTES, 'UTF-8') . '"'
            . ' id="ff_elem' . $elementId . '"' . $suffix . '/>' . "\n";
    }
}
