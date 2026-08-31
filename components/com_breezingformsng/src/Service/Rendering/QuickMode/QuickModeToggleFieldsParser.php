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

/**
 * Parses the legacy QuickMode toggle-field rule syntax.
 */
final class QuickModeToggleFieldsParser
{
    public function parse(string $code): string
    {
        $parsed = [];
        $lines = explode("\n", str_replace("\r", '', $code));

        foreach ($lines as $line) {
            $tokens = explode(' ', trim($line));
            if (count($tokens) < 8) {
                continue;
            }

            $parsed[] = [
                'action' => $tokens[0],
                'state' => $tokens[1],
                'tCat' => $tokens[2],
                'tName' => $tokens[3],
                'statement' => $tokens[4],
                'sName' => $tokens[5],
                'condition' => $tokens[6],
                'value' => implode(' ', array_slice($tokens, 7)),
            ];
        }

        return (string) json_encode(
            $parsed,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}
