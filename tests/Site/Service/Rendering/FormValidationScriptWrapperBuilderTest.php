<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormValidationScriptWrapperBuilder;

final class FormValidationScriptWrapperBuilderTest extends TestCase
{
    public function testBuildsValidationScriptEnvelope(): void
    {
        $builder = new FormValidationScriptWrapperBuilder();

        self::assertSame(
            "<script type=\"text/javascript\">\n<!--\n\nfiles();captcha();",
            $builder->open('files();', 'captcha();')
        );
        self::assertSame("//-->\r\n</script>\r\n", $builder->close("\r\n"));
    }
}
