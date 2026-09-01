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

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the deferred scripts used after QuickMode rendering.
 */
final class PostRenderScriptBuilder
{
    /**
     * Build a deferred call for a renderer-produced JavaScript function.
     */
    public function build(string $functionName): string
    {
        return '<script type="text/javascript"><!--' . "\n"
            . 'if(typeof ' . $functionName . ' != "undefined") { ' . "\n"
            . '    if(typeof JQuery != "undefined" && typeof bfToggleFieldsLoaded != "undefined" '
            . '){' . "\n"
            . '        JQuery(document).ready(function(){' . "\n"
            . '            let waitForToggleFields = setInterval(function(){' . "\n"
            . '                if(bfToggleFieldsLoaded && bfToggleFieldsLoaded){' . "\n"
            . '                    clearInterval(waitForToggleFields);' . "\n"
            . '                    ' . $functionName . '();' . "\n"
            . '                }' . "\n"
            . '            }, 100); ' . "\n"
            . '        });' . "\n"
            . '    }else{' . "\n"
            . '        ' . $functionName . '();' . "\n"
            . '    }' . "\n"
            . '}' . "\n"
            . '//--></script>' . "\n";
    }
}
