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

/** Builds the Query List select-all JavaScript callback. */
final class QueryListSelectAllScriptBuilder
{
    public function build(string $newline): string
    {
        return 'function ff_selectAllQueryRows(id,checked)' . $newline .
            '{' . $newline .
            '    if (!ff_queryCheckbox[id]) return;' . $newline .
            '    var cnt = ff_queryRows[id].length;' . $newline .
            '    var pagesize = ff_queryPageSize[id];' . $newline .
            '    if (pagesize > 0) {' . $newline .
            '        lastpage = parseInt((cnt+pagesize-1)/pagesize);' . $newline .
            '        if (lastpage == 1)' . $newline .
            '           pagesize = cnt;' . $newline .
            '        else {' . $newline .
            '            var currpage = ff_queryCurrPage[id];' . $newline .
            '            var p;' . $newline .
            '            for (p = 1; p < currpage; p++) cnt -= pagesize;' . $newline .
            '            if (cnt > pagesize) cnt = pagesize;' . $newline .
            '        } // if' . $newline .
            '    } // if' . $newline .
            '    var curr;' . $newline .
            '    for (curr = 0; curr < cnt; curr++)' . $newline .
            '        document.getElementById(\'ff_cb\'+id+\'_\'+curr).checked = checked;' . $newline .
            '    for (curr = cnt; curr < pagesize; curr++)' . $newline .
            '        document.getElementById(\'ff_cb\'+id+\'_\'+curr).checked = false;' . $newline .
            '    if (ff_queryCheckbox[id]==1)' . $newline .
            '        document.getElementById(\'ff_cb\'+id).checked = checked;' . $newline .
            '} // ff_selectAllQueryRows';
    }
}
