<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared body of the plupload FilesAdded callback. */
// phpcs:disable Generic.Files.LineLength
final class QuickModeUploadFileAddedHandlerBuilder
{
    public static function build(
        int $elementId,
        string $cancelImagePath,
        bool $bootstrapMarkup,
        string $multiSelection,
        int $maxBytes,
        string $extensions,
        string $tooLargeMessage,
        string $extensionMessage,
        string $newline = "\n"
    ): string {
        return '                                                                        for (var i in files) {' . $newline .
            "                                                                            if(typeof files[i].id != 'undefined' && files[i].id != null){" . $newline .
            "                                                                                var error = false;" . $newline .
            "                                                                                var fsize = '';" . $newline .
            "                                                                                if(typeof files[i].size != 'undefined'){" . $newline .
            "                                                                                    fsize = '(' + plupload.formatSize(files[i].size) + ') ';" . $newline .
            "                                                                                }" . $newline .
            QuickModeUploadQueueItemMarkupBuilder::build($elementId, $cancelImagePath, $bootstrapMarkup, false) . $newline .
            QuickModeUploadCancelScriptBuilder::build($multiSelection, $elementId, $bootstrapMarkup, $newline) .
            $newline .
            QuickModeUploadValidationScriptBuilder::build(
                $maxBytes,
                strtolower($extensions),
                $tooLargeMessage,
                $extensionMessage,
                $newline
            ) . $newline .
            '                                                                            }' . $newline .
            '                                                                        }';
    }
}
// phpcs:enable Generic.Files.LineLength
