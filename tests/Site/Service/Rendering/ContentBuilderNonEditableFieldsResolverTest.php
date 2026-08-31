<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderNonEditableFieldsResolver;

final class ContentBuilderNonEditableFieldsResolverTest extends TestCase
{
    public function testResolvesFieldsForTheRequestedContentBuilderForm(): void
    {
        $requestedId = null;
        $resolver = new ContentBuilderNonEditableFieldsResolver(
            static function (int $contentBuilderId) use (&$requestedId): array {
                $requestedId = $contentBuilderId;

                return [12, 'status'];
            }
        );

        self::assertSame([12, 'status'], $resolver->resolve(37));
        self::assertSame(37, $requestedId);
    }

    public function testPreservesAnEmptyFieldList(): void
    {
        $resolver = new ContentBuilderNonEditableFieldsResolver(
            static fn(int $contentBuilderId): array => []
        );

        self::assertSame([], $resolver->resolve(37));
    }
}
