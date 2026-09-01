<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared initial upload queue and FilesAdded callbacks. */
final class QuickModeUploadEntryCallbacksBuilder
{
    public static function build(
        int $elementId,
        string $cancelImagePath,
        bool $bootstrapMarkup,
        bool $hasBlankLine,
        string $multiSelection,
        int $maxBytes,
        string $extensions,
        string $tooLargeMessage,
        string $extensionMessage,
        string $newline = "\n"
    ): string {
        return QuickModeUploadQueueEntryScriptBuilder::build($newline, $hasBlankLine)
            . $newline
            . QuickModeUploadFileAddedHandlerBuilder::build(
                $elementId,
                $cancelImagePath,
                $bootstrapMarkup,
                $multiSelection,
                $maxBytes,
                $extensions,
                $tooLargeMessage,
                $extensionMessage,
                $newline
            );
    }
}
