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

/** Builds the form onload callbacks while leaving their registration to the processor. */
final class FormOnloadScriptBuilder
{
    public function initial(int|string $heightMode, int|string $height, bool $showGrid, string $newline): string
    {
        $code = "onload = function()" . $newline .
            "{" . $newline .
            "    ff_initialize('formentry');" . $newline .
            "    ff_initialize('pageentry');" . $newline;

        if ($heightMode) {
            $code .= "    ff_resizepage(" . $heightMode . ", " . $height . ");" . $newline;
        }
        if ($showGrid) {
            $code .= "    ff_showgrid();" . $newline;
        }

        return $code . "    if (ff_processor && ff_processor.traceBuffer) ff_traceWindow();" . $newline .
            "} // onload";
    }

    public function submitted(
        string $functionName,
        int|string $heightMode,
        int|string $height,
        bool $showGrid,
        int|string $status,
        string $message,
        string $newline
    ): ?string {
        if ($functionName === '' && !$heightMode && !$showGrid) {
            return null;
        }

        $code = "onload = function()" . $newline . "{" . $newline;
        if ($heightMode) {
            $code .= "    ff_resizepage(" . $heightMode . ", " . $height . ");" . $newline;
        }
        if ($showGrid) {
            $code .= "    ff_showgrid();" . $newline;
        }
        if ($functionName !== '') {
            $jsonReturn = json_encode($message, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
            if (trim((string) $jsonReturn) === '') {
                $jsonReturn = '""';
            }
            $code .= "    " . $functionName . "(" . $status . "," . $jsonReturn . ");" . $newline;
        }

        return $code . '} // onload';
    }
}
