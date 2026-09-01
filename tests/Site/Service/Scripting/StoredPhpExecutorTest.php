<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Scripting;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Scripting\StoredPhpExecutor;

final class StoredPhpExecutorTest extends TestCase
{
    public function testExecutesWithVariablesAndSuppliedObjectScope(): void
    {
        $scope = new class {
            public string $prefix = 'value';
        };

        $result = (new StoredPhpExecutor())->execute(
            $scope,
            'return $this->prefix . ":" . $value;',
            ['value' => 'processed']
        );

        self::assertSame('value:processed', $result);
    }

    public function testEmptyCodeReturnsNull(): void
    {
        $scope = new class {
        };

        self::assertNull((new StoredPhpExecutor())->execute($scope, 'return null;'));
    }
}
