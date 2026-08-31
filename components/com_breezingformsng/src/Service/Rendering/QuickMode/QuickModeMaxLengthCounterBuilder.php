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

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeMaxLengthCounterBuilder
{
    public function build(int $elementId, int $maximum, string $charactersLeft): string
    {
        return ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter' . $elementId
            . '***>(' . $maximum . ' ' . $charactersLeft . ')</span>';
    }
}
