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
 * Builds hidden inputs for additional form parameters.
 */
final class AdditionalHiddenFieldsBuilder
{
    /**
     * @param array<int|string, mixed> $parameters
     */
    public function build(array $parameters, string $indentation, string $newline = "\r\n"): string
    {
        $output = '';

        foreach ($parameters as $name => $value) {
            $output .= $indentation . '<input type="hidden" name="'
                . htmlentities((string) $name, ENT_QUOTES, 'UTF-8') . '" value="'
                . htmlentities(urlencode((string) $value), ENT_QUOTES, 'UTF-8') . '"/>' . $newline;
        }

        return $output;
    }
}
