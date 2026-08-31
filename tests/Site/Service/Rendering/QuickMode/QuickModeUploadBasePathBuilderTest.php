<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadBasePathBuilder;

final class QuickModeUploadBasePathBuilderTest extends TestCase
{
    public function testPreservesFrontendBaseUri(): void
    {
        self::assertSame(
            'https://example.test/forms/',
            (new QuickModeUploadBasePathBuilder())->build('https://example.test/forms/')
        );
    }

    public function testRemovesAdministratorSegmentFromBackendBaseUri(): void
    {
        self::assertSame(
            'https://example.test/',
            (new QuickModeUploadBasePathBuilder())->build('https://example.test/administrator/')
        );
    }

    public function testDoesNotRemoveAnUnrelatedAdministratorPathSegment(): void
    {
        self::assertSame(
            'https://example.test/administrator/path/',
            (new QuickModeUploadBasePathBuilder())->build('https://example.test/administrator/path/')
        );
    }
}
