<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeHintContentResolver;

final class QuickModeHintContentResolverTest extends TestCase
{
    public function testResolvesTheHistoricalStyleMarkerForClassicRendering(): void
    {
        self::assertSame(
            'help text',
            (new QuickModeHintContentResolver())->resolve(' label <<<style help text ', true)
        );
    }

    public function testKeepsTheOriginalHintWhenTheMarkerHasNoPrefix(): void
    {
        self::assertSame(
            '<<<style help text',
            (new QuickModeHintContentResolver())->resolve('<<<style help text', true)
        );
    }

    public function testPreservesTheHistoricalMultipleMarkerDifferenceBetweenRenderers(): void
    {
        $resolver = new QuickModeHintContentResolver();

        self::assertSame('first', $resolver->resolve('label<<<style first<<<style second', false));
        self::assertSame(
            'first<<<style second',
            $resolver->resolve('label<<<style first<<<style second', true)
        );
    }
}
