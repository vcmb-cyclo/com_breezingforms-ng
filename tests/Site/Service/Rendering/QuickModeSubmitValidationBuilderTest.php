<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeSubmitValidationBuilder;

final class QuickModeSubmitValidationBuilderTest extends TestCase
{
    public function testBuildsSubmitValidationCallback(): void
    {
        $script = (new QuickModeSubmitValidationBuilder())->build("\n");

        self::assertStringContainsString('function bf_validate_submit(element, action)', $script);
        self::assertStringContainsString('error = ff_validation(0);', $script);
        self::assertStringContainsString('bfShowErrors(error);', $script);
        self::assertStringContainsString('ff_validationFocus();', $script);
        self::assertStringContainsString('bf_restore_submitbutton();', $script);
        self::assertStringContainsString('ff_submitForm();', $script);
        self::assertStringEndsWith('} // ff_validate_submit' . "\n", $script);
    }
}
