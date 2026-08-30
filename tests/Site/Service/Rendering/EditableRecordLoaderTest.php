<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\EditableRecordLoader;

if (!interface_exists(DatabaseInterface::class)) {
    eval('namespace Joomla\\Database; interface DatabaseInterface {}');
}

final class EditableRecordLoaderTest extends TestCase
{
    public function testLoadReturnsNullWhenNoEditableRecordExists(): void
    {
        $database = new EditableRecordDatabaseDouble([]);

        self::assertNull((new EditableRecordLoader($database))->load(7, 12));
        self::assertCount(1, $database->queries);
    }

    public function testLoadReturnsLatestRecordAndItsSubrecords(): void
    {
        $entries = [(object) ['name' => 'name', 'value' => 'Alice']];
        $database = new EditableRecordDatabaseDouble(
            [(object) ['id' => 42, 'form' => 7]],
            $entries
        );

        $record = (new EditableRecordLoader($database))->load(7, 12);

        self::assertNotNull($record);
        self::assertSame(42, $record->id);
        self::assertSame($entries, $record->entries);
        self::assertCount(2, $database->queries);
    }
}

final class EditableRecordDatabaseDouble implements DatabaseInterface
{
    /** @var list<object> */
    public array $queries = [];

    /** @param list<object> $records */
    public function __construct(private array $records, private array $entries = [])
    {
    }

    public function getQuery(bool $new = false): object
    {
        return new class {
            public function select(mixed $columns): self { return $this; }
            public function from(string $table): self { return $this; }
            public function where(string $condition): self { return $this; }
            public function order(string $ordering): self { return $this; }
            public function bind(string $key, mixed $value, mixed $type): self { return $this; }
        };
    }

    public function quoteName(string|array $name): string|array
    {
        return $name;
    }

    public function setQuery(object $query, int $offset = 0, int $limit = 0): void
    {
        $this->queries[] = $query;
    }

    /** @return list<object> */
    public function loadObjectList(): array
    {
        return count($this->queries) === 1 ? $this->records : $this->entries;
    }
}
