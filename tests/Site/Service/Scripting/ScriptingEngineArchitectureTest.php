<?php

declare(strict_types=1);

namespace VcmbComponent\BreezingformsNG\Tests\Site\Service\Scripting;

use PHPUnit\Framework\TestCase;

final class ScriptingEngineArchitectureTest extends TestCase
{
    public function testErrorLoggingUsesTheJoomlaApplicationDebugConfiguration(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Scripting/ScriptingEngine.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('JDEBUG', $source);
        self::assertSame(3, substr_count($source, "->getConfig()->get('debug', false)"));
    }
}
