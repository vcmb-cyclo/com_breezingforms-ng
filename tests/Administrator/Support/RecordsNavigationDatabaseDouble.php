<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator\Support;

use Joomla\Database\DatabaseInterface;

final class RecordsNavigationDatabaseDouble implements DatabaseInterface
{
    public RecordsNavigationQueryDouble $query;

    /** @param list<int> $recordIds */
    public function __construct(private readonly array $recordIds)
    {
    }

    public function getQuery(bool $new = false): RecordsNavigationQueryDouble
    {
        return $this->query = new RecordsNavigationQueryDouble();
    }

    public function quoteName(string|array $name): string|array
    {
        return $name;
    }

    public function setQuery(object $query, int $offset = 0, int $limit = 0): void
    {
    }

    /** @return list<int> */
    public function loadColumn(): array
    {
        return $this->recordIds;
    }
}
