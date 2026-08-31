<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Wraps the ContentBuilder readonly-field script in its historical tag.
 */
final class ContentBuilderReadonlyScriptWrapperBuilder
{
    public function build(string $script, string $newline = "\n"): string
    {
        return '<script type="text/javascript">' . $newline
            . '<!--' . $newline
            . $script
            . '//-->' . $newline
            . '</script>' . $newline;
    }
}
