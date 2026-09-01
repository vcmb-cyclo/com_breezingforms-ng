<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class BFTextTranslationTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testLegacyBreezingFormsKeysResolveToTheModernComponentKeys(): void
    {
        eval(<<<'PHP'
namespace Joomla\CMS {
    final class Factory
    {
        public static object $application;

        public static function getApplication(): object
        {
            return self::$application;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return $key === 'COM_BREEZINGFORMSNG_ID' ? 'ID' : $key;
        }
    }
}
PHP);

        $language = new class {
            private bool $loaded = false;

            public function hasKey(string $key): bool
            {
                return $this->loaded && $key === 'COM_BREEZINGFORMSNG_ID';
            }

            public function load(string $extension): void
            {
                $this->loaded = $extension === 'com_breezingformsng';
            }
        };

        \Joomla\CMS\Factory::$application = new class ($language) {
            public function __construct(private readonly object $language)
            {
            }

            public function getLanguage(): object
            {
                return $this->language;
            }
        };

        require_once __DIR__ . '/../../administrator/components/com_breezingformsng/plugins/bfcompat/src/Compat/BFText.php';

        self::assertSame('ID', \BFText::_('COM_BREEZINGFORMS_ID'));
    }
}
