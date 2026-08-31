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
 * Resolves an existing ContentBuilder signature file.
 */
final class ContentBuilderSignatureFileResolver
{
    public function resolve(string $directory, string $fileName): ?string
    {
        if ($fileName === '') {
            return null;
        }

        $path = $directory . $fileName;

        return file_exists($path) ? $path : null;
    }
}
