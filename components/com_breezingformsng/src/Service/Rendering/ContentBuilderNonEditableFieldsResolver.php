<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use Closure;

/** Resolves the ContentBuilder fields that must remain non-editable. */
final class ContentBuilderNonEditableFieldsResolver
{
    /**
     * @param Closure(int): array<int|string> $loadFields
     */
    public function __construct(private readonly Closure $loadFields)
    {
    }

    /**
     * @return array<int|string>
     */
    public function resolve(int $contentBuilderId): array
    {
        return ($this->loadFields)($contentBuilderId);
    }
}
