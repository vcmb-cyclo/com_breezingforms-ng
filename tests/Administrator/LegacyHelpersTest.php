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

    public function testNotificationHelpersUseTheJoomla6MailerApi(): void
    {
        $helpers = file_get_contents(__DIR__ . '/../../administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php');
        $sofort = file_get_contents(__DIR__ . '/../../components/com_breezingformsng/src/Service/Callback/SofortCallback.php');
        $submission = file_get_contents(__DIR__ . '/../../components/com_breezingformsng/src/Service/Submission/SubmissionEngine.php');

        self::assertIsString($helpers);
        self::assertIsString($sofort);
        self::assertIsString($submission);
        self::assertStringContainsString('->addRecipient(', $helpers);
        self::assertStringContainsString('->addAttachment(', $helpers);
        self::assertStringContainsString('->isHtml(', $helpers);
        self::assertStringContainsString('->send()', $helpers);
        self::assertStringNotContainsString('->AddAddress(', $helpers . $sofort . $submission);
        self::assertStringNotContainsString('->Send()', $helpers . $sofort . $submission);
    }
}
