<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/** Builds the JavaScript navigation markup for a Query List. */
final class QueryListNavigationBuilder
{
    /**
     * @param array{start: string, previous: string, next: string, end: string} $labels
     */
    public function build(array $labels, string $newline): string
    {
        return '        if (pagenav<=4) {' . $newline .
            '            if (page>1) navi += \'<a href="javascript:ff_dispQueryPage(\'+id+\',1);">\';' . $newline .
            '            navi += \'&lt;&lt;\';' . $newline .
            '            if (pagenav<=2) navi += \' ' . $labels['start'] . '\';' . $newline .
            '            if (page>1) navi += \'<\/a>\';' . $newline .
            '            navi += \' \';' . $newline .
            '            if (page>1) navi += \'<a href="javascript:ff_dispQueryPage(\''
            . '+id+\',\'+(page-1)+\');">\';' . $newline .
            '            navi += \'&lt;\';' . $newline .
            '            if (pagenav<=2) navi += \' ' . $labels['previous'] . '\';' . $newline .
            '            if (page>1) navi += \'<\/a>\';' . $newline .
            '            navi += \' \';' . $newline .
            '        } // if' . $newline .
            '        if (pagenav % 2) {' . $newline .
            '            for (p = 1; p <= lastpage; p++)' . $newline .
            '                if (p == page) ' . $newline .
            '                    navi += p+\' \';' . $newline .
            '                else' . $newline .
            '                    navi += \'<a href="javascript:ff_dispQueryPage(\''
            . '+id+\',\'+p+\');">\'+p+\'<\/a> \';' . $newline .
            '        } // if' . $newline .
            '        if (pagenav<=4) {' . $newline .
            '            if (page<lastpage) navi += \'<a href="javascript:ff_dispQueryPage(\''
            . '+id+\',\'+(page+1)+\');">\';' . $newline .
            '            if (pagenav<=2) navi += \'' . $labels['next'] . ' \';' . $newline .
            '            navi += \'&gt;\';' . $newline .
            '            if (page<lastpage) navi += \'<\/a>\';' . $newline .
            '            navi += \' \';' . $newline .
            '            if (page<lastpage) navi += \'<a href="javascript:ff_dispQueryPage(\''
            . '+id+\',\'+lastpage+\');">\';' . $newline .
            '            if (pagenav<=2) navi += \'' . $labels['end'] . ' \';' . $newline .
            '            navi += \'&gt;&gt;\';' . $newline .
            '            if (page<lastpage) navi += \'<\/a>\';' . $newline .
            '        } // if';
    }
}
