<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/** Builds the JavaScript library entries used by Query List pagination. */
final class QueryListStateLibraryBuilder
{
    /**
     * @return list<array{0: string, 1: string}>
     */
    public function build(string $queryCode, string $newline): array
    {
        return [
            ['ff_queryCurrPage', 'var ff_queryCurrPage = new Array();'],
            ['ff_queryPageSize', 'var ff_queryPageSize = new Array();'],
            ['ff_queryCols', 'var ff_queryCols = new Array();'],
            ['ff_queryCheckbox', 'var ff_queryCheckbox = new Array();'],
            ['ff_queryHeader', 'var ff_queryHeader = new Array();'],
            ['ff_queryPagenav', 'var ff_queryPagenav = new Array();'],
            ['ff_queryRows', 'var ff_queryRows = new Array();' . $newline . $queryCode],
        ];
    }
}
