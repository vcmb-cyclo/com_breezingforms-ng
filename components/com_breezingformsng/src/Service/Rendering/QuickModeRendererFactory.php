<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use HTML_facileFormsProcessor;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\BootstrapRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\ClassicRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\OnePageRenderer;

/** Creates the QuickMode renderer selected by template metadata. */
final class QuickModeRendererFactory
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function create(HTML_facileFormsProcessor $processor, array $metadata): object
    {
        if (($metadata['themebootstrapThemeEngine'] ?? '') === 'bootstrap') {
            if (!empty($metadata['themebootstrapMode'])) {
                return new OnePageRenderer($processor);
            }

            return new BootstrapRenderer($processor);
        }

        return new ClassicRenderer($processor);
    }
}
