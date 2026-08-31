<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModePreviousPageValidationBuilder;

final class QuickModePreviousPageValidationBuilderTest extends TestCase
{
    public function testBuildsPreviousPageValidationCallback(): void
    {
        $script = (new QuickModePreviousPageValidationBuilder())->build("\n");

        self::assertStringContainsString('function bf_validate_prevpage(page)', $script);
        self::assertStringContainsString('error = ff_validation(ff_currentpage);', $script);
        self::assertStringContainsString('bfShowErrors(error);', $script);
        self::assertStringContainsString('JQuery("#bfPage"+(page)).ScrollTo({offsetTop: 50});', $script);
        self::assertStringContainsString('/* need to test this */', $script);
        self::assertStringEndsWith('} // ff_validate_prevpage' . "\n", $script);
    }
}
