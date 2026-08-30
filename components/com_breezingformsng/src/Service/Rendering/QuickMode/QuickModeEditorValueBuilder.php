<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/**
 * Builds the JavaScript expression used to read a Joomla editor value.
 */
final class QuickModeEditorValueBuilder
{
    public static function build(mixed $editor): string
    {
        return 'Joomla.editors.instances[' . json_encode($editor) . '].getValue()';
    }
}
