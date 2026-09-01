<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFormDataLoader;

if (!interface_exists(DatabaseInterface::class)) {
    eval('namespace Joomla\\Database; interface DatabaseInterface {}');
}

if (!class_exists('Joomla\\Database\\ParameterType')) {
    eval('namespace Joomla\\Database; final class ParameterType { public const INTEGER = 1; }');
}

final class ContentBuilderFormDataLoaderTest extends TestCase
{
    public function testLoadsThePublishedFormDefinitionWithAnIntegerBinding(): void
    {
        $database = new ContentBuilderFormDataDatabaseDouble([
            'reference_id' => 19,
            'published_only' => 1,
        ]);

        self::assertSame(
            ['reference_id' => 19, 'published_only' => 1],
            (new ContentBuilderFormDataLoader($database))->load(23)
        );
        self::assertSame(
            ['id = :cbFormId', 'published = 1'],
            $database->query->where
        );
        self::assertSame([[':cbFormId', 23, 1]], $database->query->bindings);
    }

    public function testReturnsNullWhenTheFormIsNotFoundOrNotPublished(): void
    {
        $database = new ContentBuilderFormDataDatabaseDouble(null);

        self::assertNull((new ContentBuilderFormDataLoader($database))->load(23));
    }
}

final class ContentBuilderFormDataDatabaseDouble implements DatabaseInterface
{
    public ContentBuilderFormDataQueryDouble $query;

    /** @param array<string, mixed>|null $data */
    public function __construct(private readonly ?array $data)
    {
    }

    public function getQuery(bool $new = false): object
    {
        return $this->query = new ContentBuilderFormDataQueryDouble();
    }

    public function quoteName(string|array $name): string|array
    {
        return $name;
    }

    public function setQuery(object $query, int $offset = 0, int $limit = 0): void
    {
    }

    /** @return array<string, mixed>|null */
    public function loadAssoc(): ?array
    {
        return $this->data;
    }
}

final class ContentBuilderFormDataQueryDouble
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
