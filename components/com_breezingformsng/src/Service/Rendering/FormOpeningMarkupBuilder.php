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
 * Builds the opening markup for the form wrapper.
 */
final class FormOpeningMarkupBuilder
{
    public function build(string $formId, string $className): string
    {
        return '<div id="ff_formdiv' . $formId . '" class="bfFormDiv'
            . ($className !== '' ? ' ' . $className : '') . '">';
    }
}
