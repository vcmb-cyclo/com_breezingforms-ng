<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

use PHPUnit\Framework\TestCase;

final class JoomlaInstallSmokeScriptTest extends TestCase
{
    public function testContentBuilderRuntimeContextIsLoadedBeforeTheBFNGLoaders(): void
    {
        $source = file_get_contents(__DIR__ . '/../../scripts/joomla-install-smoke.sh');

        self::assertIsString($source);

        $runtimeContextPosition = strpos($source, 'src/Helper/RuntimeContextHelper.php');
        $associationLoaderPosition = strpos($source, 'ContentBuilderFormAssociationLoader.php');

        self::assertIsInt($runtimeContextPosition);
        self::assertIsInt($associationLoaderPosition);
        self::assertLessThan($associationLoaderPosition, $runtimeContextPosition);
    }

    public function testContentBuilderLoaderCommandStartsAWebApplicationContext(): void
    {
        $source = file_get_contents(__DIR__ . '/../../scripts/joomla-install-smoke.sh');

        self::assertIsString($source);
        self::assertStringContainsString('docker exec -e HTTP_HOST=localhost', $source);
        self::assertStringContainsString('Factory::$application = $app;', $source);
    }
}
