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

    public function testDoubleOptInUsesTheConcreteJoomlaMailerContract(): void
    {
        $source = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Submission/SubmissionEngine.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('use Joomla\\CMS\\Mail\\Mail;', $source);
        self::assertStringContainsString('/** @var Mail $mailer */', $source);
        self::assertStringContainsString('$mailer->isHtml(true);', $source);
    }

    public function testSubmissionFlowContainsNoDisabledLegacyDebugOrPaymentModalBranches(): void
    {
        $submissionSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Submission/SubmissionEngine.php'
        );
        $facadeSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php'
        );
        $runtimeSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Runtime/CodeToolsRuntime.php'
        );

        self::assertIsString($submissionSource);
        self::assertIsString($facadeSource);
        self::assertIsString($runtimeSource);
        self::assertStringNotContainsString('$j15', $submissionSource);
        self::assertStringNotContainsString('SqueezeBox.loadModal', $submissionSource);
        self::assertStringNotContainsString('$halt', $submissionSource);
        self::assertStringNotContainsString('_FF_DEBUG_', $facadeSource . $runtimeSource);
    }

    public function testServerRecaptchaVerificationIsInjectedFromItsDedicatedService(): void
    {
        $submissionSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Submission/SubmissionEngine.php'
        );
        $facadeSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php'
        );

        self::assertIsString($submissionSource);
        self::assertIsString($facadeSource);
        self::assertStringContainsString('private readonly RecaptchaVerifier $recaptchaVerifier', $submissionSource);
        self::assertStringContainsString('$this->recaptchaVerifier->verify(', $submissionSource);
        self::assertStringNotContainsString('new RecaptchaVerifier())->verify(', $submissionSource);
        self::assertStringContainsString('new RecaptchaVerifier()', $facadeSource);
        self::assertStringNotContainsString('google.com/recaptcha', $submissionSource);
    }

    public function testUploadFacadeDelegatesStorageAndStatusMappingToSubmissionEngine(): void
    {
        $submissionSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Service/Submission/SubmissionEngine.php'
        );
        $facadeSource = file_get_contents(
            __DIR__ . '/../../../../components/com_breezingformsng/src/Support/processor_facade.php'
        );

        self::assertIsString($submissionSource);
        self::assertIsString($facadeSource);
        self::assertStringContainsString('public function saveUpload(', $submissionSource);
        self::assertStringContainsString('$this->uploadRuntime()->store(', $submissionSource);
        self::assertStringContainsString('return $this->submissionEngine()->saveUpload(', $facadeSource);
        self::assertStringNotContainsString('UploadError::', $facadeSource);
    }
}
