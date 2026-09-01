<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared initial upload queue entry callback. */
// phpcs:disable Generic.Files.LineLength
final class QuickModeUploadQueueEntryScriptBuilder
{
    public static function build(string $newline, bool $hasBlankLine = false): string
    {
        $optionalBlankLine = $hasBlankLine ? $newline : '';

        return "                                                                uploader.bind('FilesAdded', function(up, files) {" . $newline . $optionalBlankLine .
            '                                                                        for (var i in files) {' . $newline .
            "                                                                                if(typeof files[i].id != 'undefined' && files[i].id != null){" . $newline .
            "                                                                                    var fsize = '';" . $newline .
            "                                                                                    if(typeof files[i].size != 'undefined'){" . $newline .
            "                                                                                        fsize = '(' + plupload.formatSize(files[i].size) + ') ';" . $newline .
            "                                                                                    }" . $newline .
            "                                                                                    if(typeof bfUploadFileAdded == 'function'){" . $newline .
            '                                                                                        bfUploadFileAdded(files[i]);' . $newline .
            "                                                                                    }" . $newline .
            "                                                                                    JQuery('#bfFileQueue').append( '<div id=\"' + files[i].id + 'queue\">' + (iOS ? '' : files[i].name.replace(/[/\\?%*:|\"<>]/g, '')) + ' '+fsize+'<b></b></div>' );" . $newline .
            '                                                                                }' . $newline .
            '                                                                        }';
    }
}
// phpcs:enable Generic.Files.LineLength
