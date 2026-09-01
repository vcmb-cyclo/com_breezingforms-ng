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

/** Builds the JavaScript that refreshes visible Query List rows. */
final class QueryListRowsRefreshBuilder
{
    public function build(string $newline): string
    {
        return '    var qrows = ff_queryRows[id];' . $newline .
            '    var cnt = qrows.length;' . $newline .
            '    var currpage = ff_queryCurrPage[id];' . $newline .
            '    var pagesize = ff_queryPageSize[id];' . $newline .
            '    var pagenav = ff_queryPagenav[id];' . $newline .
            '    var lastpage = 1;' . $newline .
            '    if (pagesize > 0) {' . $newline .
            '        lastpage = parseInt((cnt+pagesize-1)/pagesize);' . $newline .
            '        if (lastpage == 1) pagesize = cnt;' . $newline .
            '    } // if' . $newline .
            '    if (page < 1) page = 1;' . $newline .
            '    if (page > lastpage) page = lastpage;' . $newline .
            '    if (!forced && page == currpage) return;' . $newline .
            '    var p, c;' . $newline .
            '    for (p = 1; p < page; p++) cnt -= pagesize;' . $newline .
            '    if (cnt > pagesize) cnt = pagesize;' . $newline .
            '    var start = (page-1) * pagesize;' . $newline .
            '    var rows = document.getElementById(\'ff_elem\'+id).rows;' . $newline .
            '    var cols = ff_queryCols[id];' . $newline .
            '    var checkbox = ff_queryCheckbox[id];' . $newline .
            '    var header = ff_queryHeader[id];' . $newline .
            '    for (p = 0; p < cnt; p++) {' . $newline .
            '        var qrow = qrows[start+p];' . $newline .
            '        var row = rows[header+p];' . $newline .
            '        var cc = 0;' . $newline .
            '        for (c = 0; c < cols.length; c++)' . $newline .
            '            if (cols[c]) {' . $newline .
            '                if (c==0 && checkbox>0) {' . $newline .
            '                    document.getElementById(\'ff_cb\'+id+\'_\'+p).value = qrow[c];' . $newline .
            '                    cc++;' . $newline .
            '                } else' . $newline .
            '                    row.cells[cc++].innerHTML = qrow[c];' . $newline .
            '            } // if' . $newline .
            '        row.style.display = \'\';' . $newline .
            '    } // for' . $newline .
            '    for (p = cnt; p < pagesize; p++) {' . $newline .
            '        var row = rows[p+header];' . $newline .
            '        row.style.display = \'none\';' . $newline .
            '    } // for';
    }
}
