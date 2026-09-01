<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service;

use PHPUnit\Framework\TestCase;

final class EngineDispatcherContainerTest extends TestCase
{
    public function testComponentProviderRegistersEngineDispatcherWithJoomlaServices(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../administrator/components/com_breezingformsng/services/provider.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('EngineDispatcher::class', $source);
        self::assertStringContainsString('$container->set(', $source);
        self::assertStringContainsString('DatabaseInterface::class', $source);
        self::assertStringContainsString('MailerFactoryInterface::class', $source);
        self::assertStringContainsString('CacheControllerFactoryInterface::class', $source);
    }

    public function testFrontendBootstrapResolvesDispatcherFromTheContainer(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../components/com_breezingformsng/breezingformsng.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('$container->get(EngineDispatcher::class)', $source);
        self::assertStringNotContainsString('new EngineDispatcher(', $source);
    }
}
