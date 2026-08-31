<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service;

use PHPUnit\Framework\TestCase;

final class FormRendererArchitectureTest extends TestCase
{
    public function testApplicationContextVariableIsInitializedBeforeItsBranches(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../components/com_breezingformsng/src/Service/FormRenderer.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            '$ff_applic = (string) ($context[\'ff_applic\'] ?? \'\');',
            $source
        );
        self::assertStringNotContainsString('isset($ff_applic)', $source);
    }
}
