<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the OnePage Thank You modal close redirect callback. */
final class QuickModeRemodalCloseScriptBuilder
{
    public static function build(string $redirectUrl, string $newline): string
    {
        return '                   function bf_remodal_close(){' . $newline .
            '                        if(typeof crbc_cart_url != "undefined"){' . $newline .
            '                            location.href = crbc_cart_url;' . $newline .
            '                        }else{' . $newline .
            '                            location.href = ' . $redirectUrl . ';' . $newline .
            '                        }' . $newline .
            '                   }' . $newline;
    }
}
