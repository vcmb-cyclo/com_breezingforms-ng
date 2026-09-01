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
        return self::queueEntryScript($newline, $hasBlankLine)
            . $newline
            . self::fileAddedHandler(
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

    // phpcs:disable Generic.Files.LineLength
    private static function queueEntryScript(string $newline, bool $hasBlankLine): string
    {
        $optionalBlankLine = $hasBlankLine ? $newline : '';

        return "                                                                uploader.bind('FilesAdded', function(up, files) {" . $newline . $optionalBlankLine .
            '                                                                        for (var i in files) {' . $newline .
            "                                                                                if(typeof files[i].id != 'undefined' && files[i].id != null){" . $newline .
            "                                                                                    var fsize = '';" . $newline .
            "                                                                                    if(typeof files[i].size != 'undefined'){" . $newline .
            "                                                                                        fsize = '(' + plupload.formatSize(files[i].size) + ') ';" . $newline .
            '                                                                                    }' . $newline .
            "                                                                                    if(typeof bfUploadFileAdded == 'function'){" . $newline .
            '                                                                                        bfUploadFileAdded(files[i]);' . $newline .
            "                                                                                    }" . $newline .
            "                                                                                    JQuery('#bfFileQueue').append( '<div id=\"' + files[i].id + 'queue\">' + (iOS ? '' : files[i].name.replace(/[/\\?%*:|\"<>]/g, '')) + ' '+fsize+'<b></b></div>' );" . $newline .
            '                                                                                }' . $newline .
            '                                                                        }';
    }

    private static function fileAddedHandler(
        int $elementId,
        string $cancelImagePath,
        bool $bootstrapMarkup,
        string $multiSelection,
        int $maxBytes,
        string $extensions,
        string $tooLargeMessage,
        string $extensionMessage,
        string $newline
    ): string {
        return '                                                                        for (var i in files) {' . $newline .
            "                                                                            if(typeof files[i].id != 'undefined' && files[i].id != null){" . $newline .
            "                                                                                var error = false;" . $newline .
            "                                                                                var fsize = '';" . $newline .
            "                                                                                if(typeof files[i].size != 'undefined'){" . $newline .
            "                                                                                    fsize = '(' + plupload.formatSize(files[i].size) + ') ';" . $newline .
            '                                                                                }' . $newline .
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
    // phpcs:enable Generic.Files.LineLength
}
