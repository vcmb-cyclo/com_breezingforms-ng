<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeSubmitButtonRestoreBuilder;

final class QuickModeSubmitButtonRestoreBuilderTest extends TestCase
{
    public function testBuildsSubmitButtonRestorationCallback(): void
    {
        $script = (new QuickModeSubmitButtonRestoreBuilder())->build("\n");

        self::assertStringContainsString('function bf_restore_submitbutton(){', $script);
        self::assertStringContainsString('clone(true)', $script);
        self::assertStringContainsString('replaceWith( JQuery(cloned_submit) )', $script);
        self::assertStringContainsString('attr("id","bfSubmitButton")', $script);
        self::assertStringContainsString('Ladda.bind("#bfSubmitButton");', $script);
    }
}
