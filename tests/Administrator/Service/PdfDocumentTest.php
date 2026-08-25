<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator\Service;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Administrator\Service\PdfDocument;

final class PdfDocumentTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    private static string $fontDirectory;

    public static function setUpBeforeClass(): void
    {
        self::$fontDirectory = sys_get_temp_dir() . '/bfng-pdf-font-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir(self::$fontDirectory));
        self::assertNotFalse(file_put_contents(self::$fontDirectory . '/myfontb.json', '{}'));

        if (!defined('JPATH_ADMINISTRATOR')) {
            define('JPATH_ADMINISTRATOR', self::ROOT . '/administrator');
        }

        if (!defined('K_PATH_FONTS')) {
            define('K_PATH_FONTS', self::$fontDirectory . '/');
        }

        if (!class_exists('TCPDF', false)) {
            class_alias(\stdClass::class, 'TCPDF');
        }

        require_once self::ROOT . '/administrator/components/com_breezingformsng/src/Service/PdfDocument.php';
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$fontDirectory . '/myfontb.json');
        rmdir(self::$fontDirectory);
    }

    public function testImportUsesTcpdfNormalizationForMixedCaseFontNames(): void
    {
        self::assertSame('myfontb', PdfDocument::importTtfFont('/fonts/MyFont-Bold.ttf'));
    }
}
