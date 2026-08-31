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

    private function sectionBefore(string $source, string $needle): string
    {
        $position = strpos($source, $needle);

        self::assertIsInt($position);

        return substr($source, max(0, $position - 250), 250);
    }
}
