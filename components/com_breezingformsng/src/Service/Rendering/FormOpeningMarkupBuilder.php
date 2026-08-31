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
 * Builds the opening markup for the form wrappers.
 */
final class FormOpeningMarkupBuilder
{
    public function build(string $formId, string $className, bool $legacyWrap, string $newline = "\r\n"): string
    {
        $markup = '<div id="ff_formdiv' . $formId . '" class="bfFormDiv'
            . ($className !== '' ? ' ' . $className : '') . '"';

        if ($legacyWrap) {
            return $markup . '><div class="bfPage-tl"><div class="bfPage-tr">'
                . '<div class="bfPage-t"></div></div></div><div class="bfPage-l">'
                . '<div class="bfPage-r"><div class="bfPage-m bfClearfix">' . $newline;
        }

        return $markup . '>';
    }
}
