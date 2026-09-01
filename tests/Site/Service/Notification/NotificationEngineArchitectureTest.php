<?php

declare(strict_types=1);

namespace VcmbComponent\BreezingformsNG\Tests\Site\Service\Notification;

use PHPUnit\Framework\TestCase;

final class NotificationEngineArchitectureTest extends TestCase
{
    public function testNotificationEngineDelegatesExportsAndMailToExportEngine(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Notification/NotificationEngine.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            'private readonly ExportEngine $exportEngine',
            $source
        );

        foreach (['expxml', 'expcsv', 'exppdf', 'sendMail'] as $method) {
            self::assertStringContainsString('$this->exportEngine->' . $method . '(', $source);
            self::assertStringNotContainsString('$this->processor->' . $method . '(', $source);
        }
    }

    public function testNotificationTranslationsStayInsideNotificationEngine(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Notification/NotificationEngine.php'
        );

        self::assertIsString($source);

        foreach (['getFormTitleTranslated', 'getFieldTranslated'] as $method) {
            self::assertStringContainsString('$this->' . $method . '(', $source);
            self::assertStringNotContainsString('$this->processor->' . $method . '(', $source);
        }
    }

    public function testFacadeSharesItsExportEngineWithNotificationEngine(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            'new NotificationEngine($this, $this->exportEngine())',
            $source
        );
    }
}
