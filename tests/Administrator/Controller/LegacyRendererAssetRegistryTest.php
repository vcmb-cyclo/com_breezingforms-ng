<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyRendererAssetRegistryTest extends TestCase
{
    #[DataProvider('controllerProvider')]
    public function testLegacyRendererControllersRegisterTheComponentAssetRegistry(string $controller): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../administrator/components/com_breezingformsng/src/Controller/' . $controller
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            "getRegistry()->addExtensionRegistryFile('com_breezingformsng')",
            $source
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function controllerProvider(): array
    {
        return [
            'scripts' => ['ScriptsController.php'],
            'pieces' => ['PiecesController.php'],
        ];
    }
}
