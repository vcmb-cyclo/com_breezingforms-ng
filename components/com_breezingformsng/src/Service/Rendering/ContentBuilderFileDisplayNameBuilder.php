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
 * Resolves a safe display name from a ContentBuilder file path.
 */
final class ContentBuilderFileDisplayNameBuilder
{
    public function build(string $wrappedPath): string
    {
        $displayName = htmlspecialchars(basename($wrappedPath), ENT_QUOTES, 'UTF-8');

        return str_replace('&lt;br&gt;', '<br>', $displayName);
    }
}
