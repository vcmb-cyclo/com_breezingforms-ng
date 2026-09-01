<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

use PHPUnit\Framework\TestCase;

final class InstallerScriptArchitectureTest extends TestCase
{
    public function testPreflightDocumentsItsBooleanContract(): void
    {
        $source = file_get_contents(__DIR__ . '/../../script.php');

        self::assertIsString($source);
        self::assertStringContainsString(
            '* @return bool',
            $this->sectionBefore($source, 'public function preflight(string $type, $parent): bool')
        );
    }

    public function testPluginUninstallUsesTheJoomlaSixArgumentList(): void
    {
        $source = file_get_contents(__DIR__ . '/../../script.php');

        self::assertIsString($source);
        self::assertStringNotContainsString("->uninstall('plugin', \$extensionId, 1)", $source);
        self::assertStringNotContainsString("->uninstall('plugin', \$id, 1)", $source);
        self::assertSame(2, substr_count($source, "->uninstall('plugin', \$"));
    }

    private function sectionBefore(string $source, string $needle): string
    {
        $position = strpos($source, $needle);

        self::assertIsInt($position);

        return substr($source, max(0, $position - 250), 250);
    }
}
