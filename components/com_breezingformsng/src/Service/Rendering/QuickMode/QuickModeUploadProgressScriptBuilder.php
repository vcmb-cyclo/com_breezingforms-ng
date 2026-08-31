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

/** Builds the shared plupload progress callback. */
// phpcs:disable Generic.Files.LineLength
final class QuickModeUploadProgressScriptBuilder
{
    public static function build(string $newline): string
    {
        return "                                                                uploader.bind('UploadProgress', function(up, file) {" . $newline .
            "                                                                    if(typeof JQuery('#'+file.id+'queue').get(0) != 'undefined'){" . $newline .
            "                                                                        JQuery('#'+file.id+'queue').get(0).getElementsByTagName('b')[0].innerHTML = file.percent + '% <div style=\"height: 5px;width: ' + (file.percent*1.5) + 'px;background-color: #9de24f;\"></div>';" . $newline .
            '                                                                    }' . $newline .
            '                                                                });';
    }
}
// phpcs:enable Generic.Files.LineLength
