<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php';

final class LegacyHelpersTest extends TestCase
{
    public function testUtf8ValidationAcceptsValidStringsAndRejectsInvalidBytes(): void
    {
        self::assertTrue(bf_isUTF8('Prénom'));
        self::assertTrue(bf_isUTF8("\xEF\xBB\xBFdocument"));
        self::assertFalse(bf_isUTF8("\xC3\x28"));
        self::assertFalse(bf_isUTF8(42));
    }

    public function testUtf8ValidationRecognizesBomByteArrays(): void
    {
        self::assertTrue(bf_isUTF8(["\xEF", "\xBB", "\xBF"]));
        self::assertFalse(bf_isUTF8(['a', 'b', 'c']));
        self::assertFalse(bf_isUTF8([]));
    }

    public function testSlashRemovalIsAStableNoOpOnPhp83(): void
    {
        $value = ['name' => "O'Reilly", 'nested' => ['value' => 'C:\\tmp']];

        self::assertSame($value, bf_stripslashes_deep($value));
        self::assertSame("O'Reilly", bf_stripslashes_deep("O'Reilly"));
    }
}
