<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Loads the published ContentBuilder forms associated with a BF form. */
final class ContentBuilderFormAssociationLoader
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /** @return array<int, mixed> */
    public function load(int $breezingFormsFormId): array
    {
        $query = $this->database->getQuery(true)
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__contentbuilderng_forms'))
            ->where($this->database->quoteName('type') . ' = ' . $this->database->quote('com_breezingformsng'))
            ->where($this->database->quoteName('reference_id') . ' = :referenceId')
            ->where($this->database->quoteName('published') . ' = 1')
            ->bind(':referenceId', $breezingFormsFormId, ParameterType::INTEGER);
        $this->database->setQuery($query);

        return (array) $this->database->loadColumn();
    }
}
