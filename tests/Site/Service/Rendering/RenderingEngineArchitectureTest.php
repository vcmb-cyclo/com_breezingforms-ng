<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;

final class RenderingEngineArchitectureTest extends TestCase
{
    public function testPermissionServiceIsNotRecreatedForEachPermissionOperation(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Rendering/RenderingEngine.php'
        );

        self::assertIsString($source);
        $methodStart = strpos($source, 'public function cbCheckPermissions()');
        $viewStart = strpos($source, 'public function view()');
        self::assertIsInt($methodStart);
        self::assertIsInt($viewStart);
        $method = substr($source, $methodStart, $viewStart - $methodStart);

        self::assertSame(2, substr_count($method, 'PermissionService::createFromRuntimeContext()'));
        self::assertStringNotContainsString('(PermissionService::createFromRuntimeContext())->', $method);
    }

    public function testCaptchaValidationIsDelegatedToDedicatedBuilders(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Rendering/RenderingEngine.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('captchaValidationScriptBuilder()->build(', $source);
        self::assertStringNotContainsString('private function buildCaptchaScript(', $source);
        self::assertStringNotContainsString('function bfValidateCaptcha()', $source);
    }
}
