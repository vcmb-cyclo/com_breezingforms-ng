<?php

// PHPStan stub, not a real implementation - see ../README.md.

namespace CB\Component\Contentbuilderng\Administrator\Service;

class ArticleService
{
    /**
     * @param array<mixed> $record
     * @param array<mixed> $elementsAllowed
     * @param array<mixed> $config
     */
    public function createArticle(
        int $contentbuilderngFormId,
        int $recordId,
        array $record,
        array $elementsAllowed,
        $titleField = '',
        $metadata = null,
        $config = [],
        bool $full = false,
        bool $limitedOptions = true,
        $menuCatId = null
    ): int {
        return 0;
    }
}
