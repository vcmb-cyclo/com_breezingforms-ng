<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Loads one published ContentBuilder form definition. */
final class ContentBuilderFormDataLoader
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function load(int $contentBuilderFormId): ?array
    {
        $query = $this->database->getQuery(true)
            ->select('*')
            ->from($this->database->quoteName('#__contentbuilderng_forms'))
            ->where($this->database->quoteName('id') . ' = :cbFormId')
            ->where($this->database->quoteName('published') . ' = 1')
            ->bind(':cbFormId', $contentBuilderFormId, ParameterType::INTEGER);
        $this->database->setQuery($query);
        $data = $this->database->loadAssoc();

        return is_array($data) ? $data : null;
    }
}
