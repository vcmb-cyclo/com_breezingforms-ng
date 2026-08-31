<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Submission;

use PHPUnit\Framework\TestCase;

final class SubmissionEngineArchitectureTest extends TestCase
{
    public function testFileSubmissionDataUsesTheProcessorSalesforceDataBuffer(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Submission/SubmissionEngine.php'
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('->sfadata', $source);
        self::assertStringContainsString('$this->processor->sfdata[] = array(', $source);
    }
}
