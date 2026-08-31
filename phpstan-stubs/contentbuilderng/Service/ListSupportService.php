<?php

// PHPStan stub, not a real implementation - see ../README.md.

namespace CB\Component\Contentbuilderng\Administrator\Service;

class ListSupportService
{
    public static function createFromRuntimeContext(): self
    {
        return new self();
    }

    /**
     * @return array<mixed>
     */
    public function getListNonEditableElements(int $formId): array
    {
        return [];
    }
}
