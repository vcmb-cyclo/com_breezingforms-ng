<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingFormsNG\Tests\Site\Callback;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaymentCallbackRegressionTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    /**
     * @return iterable<string, array{string}>
     */
    public static function callbackProvider(): iterable
    {
        yield 'Stripe' => ['StripeCallback'];
        yield 'PayPal' => ['PayPalCallback'];
        yield 'Sofort' => ['SofortCallback'];
    }

    #[DataProvider('callbackProvider')]
    public function testCallbacksUseJoomlaQueryBuilderAndBoundParameters(string $callback): void
    {
        $source = $this->read("components/com_breezingformsng/src/Service/Callback/{$callback}.php");

        self::assertStringContainsString('->getQuery(true)', $source);
        self::assertStringContainsString('->quoteName(', $source);
        self::assertStringContainsString('->bind(', $source);
    }

    #[DataProvider('callbackProvider')]
    public function testCallbacksDoNotPassEnglishSentencesAsTranslationKeys(string $callback): void
    {
        $source = $this->read("components/com_breezingformsng/src/Service/Callback/{$callback}.php");

        self::assertDoesNotMatchRegularExpression(
            '/Text::_\\(\\s*([\'"])(?!COM_|J[A-Z_]|DATE_).*?\\1\\s*\\)/',
            $source
        );
    }

    public function testPaymentMessagesExistInEveryPackagedLanguageFile(): void
    {
        $keys = [
            'COM_BREEZINGFORMSNG_PAYMENT_AMOUNT_CURRENCY_INVALID',
            'COM_BREEZINGFORMSNG_PAYMENT_TRANSACTION_ALREADY_PROCESSED',
            'COM_BREEZINGFORMSNG_PAYMENT_RECORD_NOT_FOUND',
            'COM_BREEZINGFORMSNG_PAYMENT_VERIFICATION_FAILED',
            'COM_BREEZINGFORMSNG_PAYMENT_VERIFICATION_EMPTY',
            'COM_BREEZINGFORMSNG_PAYMENT_TRANSACTION_ID_EMPTY',
        ];
        $files = array_merge(
            glob(self::ROOT . '/components/com_breezingformsng/language/*/com_breezingformsng.ini') ?: [],
            glob(self::ROOT . '/administrator/components/com_breezingformsng/language/*/com_breezingformsng.ini') ?: []
        );

        self::assertCount(16, $files);

        foreach ($files as $file) {
            $translations = parse_ini_file($file);

            self::assertIsArray($translations, "Invalid language file {$file}");

            foreach ($keys as $key) {
                self::assertArrayHasKey($key, $translations, "Missing {$key} in {$file}");
                self::assertNotSame('', trim((string) $translations[$key]), "Empty {$key} in {$file}");
            }
        }
    }

    private function read(string $path): string
    {
        $source = file_get_contents(self::ROOT . '/' . $path);

        self::assertNotFalse($source, "Unable to read {$path}");

        return $source;
    }
}
