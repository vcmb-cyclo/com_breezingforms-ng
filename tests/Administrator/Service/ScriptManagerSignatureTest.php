<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Vcmb\Component\BreezingformsNG\Administrator\Service\ScriptManager;

final class ScriptManagerSignatureTest extends TestCase
{
    #[DataProvider('signatureProvider')]
    public function testExtractsJavaScriptCallbackSignatures(string $code, array $expected): void
    {
        $method = new ReflectionMethod(ScriptManager::class, 'extractFunctionSignature');
        $method->setAccessible(true);

        self::assertSame($expected, $method->invoke(null, $code, 'fallback'));
    }

    public static function signatureProvider(): iterable
    {
        yield 'function declaration' => [
            'function validate(value, count = 2) {}',
            ['validate', ['value', 'count'], ['', '2']],
        ];
        yield 'function expression' => [
            'const submit = function (event) {};',
            ['submit', ['event'], ['']],
        ];
        yield 'arrow expression with rest parameter' => [
            'const render = (...items) => items;',
            ['render', ['items'], ['']],
        ];
        yield 'fallback when no signature exists' => [
            'return value;',
            ['fallback', [], []],
        ];
    }
}
