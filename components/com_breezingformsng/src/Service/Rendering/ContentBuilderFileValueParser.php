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
 * Parses the newline-separated value stored for a ContentBuilder file field.
 */
final class ContentBuilderFileValueParser
{
    /**
     * @return array{count: int, files: list<string>}
     */
    public function parse(string $value): array
    {
        $files = explode("\n", str_replace("\r", '', $value));

        return [
            'count' => count($files),
            'files' => array_values($files),
        ];
    }
}
