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

    public function testInternalScriptingOperationsDoNotRoundTripThroughTheFacade(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Scripting/ScriptingEngine.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('$this->execPiece(', $source);
        self::assertStringContainsString('$this->execQueryValue(', $source);
        self::assertStringContainsString('$this->linkcode(', $source);
        self::assertStringContainsString('$this->replaceCode(', $source);
        self::assertStringNotContainsString('$this->processor->getPieceById(', $source);
        self::assertStringNotContainsString('$this->processor->getPieceByName(', $source);
        self::assertStringNotContainsString('$this->processor->execPiece(', $source);
        self::assertStringNotContainsString('$this->processor->execQueryValue(', $source);
        self::assertStringNotContainsString('$this->processor->linkcode(', $source);
        self::assertStringNotContainsString('$this->processor->replaceCode(', $source);
        self::assertStringNotContainsString('$this->processor->compressJavascript(', $source);
    }
}
