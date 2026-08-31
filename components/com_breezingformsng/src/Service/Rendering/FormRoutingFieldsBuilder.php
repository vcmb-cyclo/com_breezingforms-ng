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
 * Builds the hidden fields preserving the current form routing parameters.
 */
final class FormRoutingFieldsBuilder
{
    public function build(string $return, string $template, string $newline = "\r\n"): string
    {
        $fields = '';

        if ($return !== '') {
            $fields .= '<input type="hidden" name="return" value="'
                . htmlentities($return, ENT_QUOTES, 'UTF-8') . '"/>' . $newline;
        }

        if ($template === 'component') {
            $fields .= '<input type="hidden" name="tmpl" value="component"/>' . $newline;
        }

        return $fields;
    }
}
