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

/** Builds the OnePage submit-button restoration callback. */
final class QuickModeSubmitButtonRestoreBuilder
{
    public function build(string $newline): string
    {
        return '                        function bf_restore_submitbutton(){' . $newline .
            '                            var cloned_submit = JQuery(orig_submit_button).clone(true);' . $newline .
            '                            var old_submit = JQuery( "#bfSubmitButton" )'
            . '.replaceWith( JQuery(cloned_submit) );' . $newline .
            '                            JQuery(old_submit).remove();' . $newline .
            '                            JQuery(cloned_submit).attr("id","bfSubmitButton");' . $newline .
            $newline .
            '                            ladda_button = JQuery( "#bfSubmitButton" ).ladda();' . $newline .
            '                            Ladda.bind("#bfSubmitButton");' . $newline .
            '                        }' . $newline;
    }
}
