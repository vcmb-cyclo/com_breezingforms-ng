<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\MobileChoiceMarkupBuilder;

final class MobileChoiceMarkupBuilderTest extends TestCase
{
    public function testBuildsMobileChoiceMarkup(): void
    {
        $html = (new MobileChoiceMarkupBuilder())->build('/form?mobile=1', 'Version mobile');

        self::assertStringContainsString('var bf_mobile_url = "\\/form?mobile=1";', $html);
        self::assertStringContainsString('<span>Version mobile</span>', $html);
        self::assertStringEndsWith('</div><div></div>', $html);
    }
}
