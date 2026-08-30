<?php

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
            . '&& typeof bfToggleFieldsLoaded != "undefined"){' . "\n"
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
