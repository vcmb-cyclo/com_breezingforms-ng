<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared plupload completion callback. */
// phpcs:disable Generic.Files.LineLength
final class QuickModeUploadCompletedScriptBuilder
{
    public static function build(string $newline): string
    {
        return "                                                                uploader.bind('FileUploaded', function(up, file, response) {" . $newline .
            "                                                                    if(response.response!=''){" . $newline .
            "                                                                        if(response.response !== null){" . $newline .
            "                                                                            alert(response.response);" . $newline .
            '                                                                        }' . $newline .
            '                                                                    }' . $newline .
            "                                                                    JQuery('#'+file.id+'queue').remove();" . $newline .
            '                                                                });';
    }
}
// phpcs:enable Generic.Files.LineLength
