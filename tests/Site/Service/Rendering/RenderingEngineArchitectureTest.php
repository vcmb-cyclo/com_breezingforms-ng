<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;

final class RenderingEngineArchitectureTest extends TestCase
{
    public function testPermissionServiceCreationIsDelegatedToTheChecker(): void
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

        self::assertSame(0, substr_count($method, 'PermissionService::createFromRuntimeContext()'));
        self::assertStringContainsString('contentBuilderPermissionChecker()->assertCanCreate(', $method);
        self::assertStringContainsString('contentBuilderPermissionChecker()->assertCanEditOrCreate(', $method);
        self::assertStringContainsString('contentBuilderRecordLoader()->load(', $method);
        self::assertStringNotContainsString('->getRecord(', $method);
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

    public function testClassicRenderingContainsNoJoomlaThreeCompatibilityStyle(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Rendering/RenderingEngine.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('fixing J3 css', $source);
        self::assertStringNotContainsString('vertical-align: text-bottom', $source);
        self::assertStringNotContainsString('vertical-align: text-top', $source);
    }
}
