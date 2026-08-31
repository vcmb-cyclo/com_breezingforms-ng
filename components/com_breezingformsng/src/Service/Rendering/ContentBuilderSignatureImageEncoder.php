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
 * Encodes an existing ContentBuilder signature image for a data URL.
 */
final class ContentBuilderSignatureImageEncoder
{
    public function encode(string $path): string
    {
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        return base64_encode($contents === false ? '' : $contents);
    }
}
