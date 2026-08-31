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
 * Builds the shared navigation-button actions used by QuickMode renderers.
 */
final class QuickModePagingActionBuilder
{
    public function previous(): string
    {
        return "ff_validate_prevpage(this, 'click');"
            . "populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}";
    }

    public function next(): string
    {
        return "ff_validate_nextpage(this, 'click');"
            . "populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}";
    }

    public function onePageNext(int $currentPage, int $nextPage): string
    {
        return 'ff_currentpage = ' . $currentPage
            . ";bf_validate_nextpage(" . $nextPage . ");"
            . "populateSummarizers();if(typeof bfRefreshAll != 'undefined'){bfRefreshAll();}";
    }

    public function cancel(): string
    {
        return "ff_resetForm(this, 'click');";
    }
}
