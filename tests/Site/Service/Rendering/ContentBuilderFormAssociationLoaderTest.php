<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFormAssociationLoader;

if (!interface_exists(DatabaseInterface::class)) {
    eval('namespace Joomla\\Database; interface DatabaseInterface {}');
}

if (!class_exists('Joomla\\Database\\ParameterType')) {
    eval('namespace Joomla\\Database; final class ParameterType { public const INTEGER = 1; }');
}

final class ContentBuilderFormAssociationLoaderTest extends TestCase
{
    public function testLoadsOnlyPublishedBreezingFormsAssociations(): void
    {
        $database = new ContentBuilderAssociationDatabaseDouble([41, 42]);

        self::assertSame([41, 42], (new ContentBuilderFormAssociationLoader($database))->load(7));
        self::assertCount(1, $database->queries);
        self::assertSame(
            [
                "type = 'com_breezingformsng'",
                'reference_id = :referenceId',
                'published = 1',
            ],
            $database->query->where
        );
        self::assertSame([[':referenceId', 7, 1]], $database->query->bindings);
    }

    public function testReturnsAnEmptyListWhenNoAssociationExists(): void
    {
        $database = new ContentBuilderAssociationDatabaseDouble([]);

        self::assertSame([], (new ContentBuilderFormAssociationLoader($database))->load(7));
    }
}

final class ContentBuilderAssociationDatabaseDouble implements DatabaseInterface
{
    /** @var list<object> */
    public array $queries = [];
    public ContentBuilderAssociationQueryDouble $query;

    /** @param array<int, mixed> $associations */
    public function __construct(private readonly array $associations)
    {
    }

    public function getQuery(bool $new = false): object
    {
        return $this->query = new ContentBuilderAssociationQueryDouble();
    }

    public function quoteName(string|array $name): string|array
    {
        return $name;
    }

    public function quote(string $value): string
    {
        return "'" . $value . "'";
    }

    public function setQuery(object $query, int $offset = 0, int $limit = 0): void
    {
        $this->queries[] = $query;
    }

    /** @return array<int, mixed> */
    public function loadColumn(): array
    {
        return $this->associations;
    }
}

final class ContentBuilderAssociationQueryDouble
{
    /** @var list<string> */
    public array $where = [];

    /** @var list<array{string, mixed, mixed}> */
    public array $bindings = [];

    public function select(mixed $columns): self
    {
        return $this;
    }

    public function from(string $table): self
    {
        return $this;
    }

    public function where(string $condition): self
    {
        $this->where[] = $condition;

        return $this;
    }

    public function bind(string $key, mixed $value, mixed $type): self
    {
        $this->bindings[] = [$key, $value, $type];

        return $this;
    }
}
