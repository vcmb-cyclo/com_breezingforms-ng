<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds shared client-side upload size and extension validation. */
// phpcs:disable Generic.Files.LineLength
final class QuickModeUploadValidationScriptBuilder
{
    public static function build(
        int $maxBytes,
        string $extensions,
        string $tooLargeMessage,
        string $extensionMessage,
        string $newline,
    ): string {
        return '                                                                                var thebytes = ' . $maxBytes . ";" . $newline .
            '                                                                                if(thebytes > 0 && typeof files[i].size != \'undefined\' && files[i].size > thebytes){' . $newline .
            '                                                                                     alert(' . $tooLargeMessage . ');' . $newline .
            '                                                                                     error = true;' . $newline .
            '                                                                                }' . $newline .
            "                                                                                var ext = files[i].name.replace(/[/\\?%*:|\"<>]/g, '').split('.').pop().toLowerCase();" . $newline .
            '                                                                                var exts = \'' . strtolower($extensions) . '\'.split(\',\');' . $newline .
            '                                                                                var found = 0;' . $newline .
            '                                                                                for (var x in exts){' . $newline .
            '                                                                                    if(exts[x] == ext){' . $newline .
            '                                                                                        found++;' . $newline .
            '                                                                                    }' . $newline .
            '                                                                                }' . $newline .
            '                                                                                if(found == 0){' . $newline .
            '                                                                                    alert(' . $extensionMessage . ');' . $newline .
            '                                                                                    error = true;' . $newline .
            '                                                                                }' . $newline .
            '                                                                                if(error){' . $newline .
            "                                                                                    JQuery('#'+files[i].id+'queue').remove();" . $newline .
            "                                                                                    JQuery('#'+files[i].id+'queueitem').remove();" . $newline .
            '                                                                                }else{' . $newline .
            '                                                                                    bfFlashUploadersLength++;' . $newline .
            '                                                                                }' . $newline .
            '                                                                                bfUploadImageThumb(files[i]);';
    }
}
// phpcs:enable Generic.Files.LineLength
