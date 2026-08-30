<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Data returned when an editable BreezingForms record is found.
 */
final class EditableRecord
{
    /**
     * @param list<object> $entries
     */
    public function __construct(
        public readonly int $id,
        public readonly array $entries
    ) {
    }
}
