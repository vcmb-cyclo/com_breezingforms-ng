<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared hidden QuickMode field markup. */
final class QuickModeHiddenFieldBuilder
{
    /**
     * @param array<string, mixed> $data
     */
    public static function build(array $data): string
    {
        $name = htmlspecialchars((string) ($data['bfName'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $value = htmlentities(trim((string) ($data['value'] ?? '')), ENT_QUOTES, 'UTF-8');
        $elementId = htmlspecialchars((string) ($data['dbId'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<input class="ff_elem" type="hidden" name="ff_nm_' . $name . '[]" '
            . 'value="' . $value . '" id="ff_elem' . $elementId . '"/>' . "\n";
    }
}
