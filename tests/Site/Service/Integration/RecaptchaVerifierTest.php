<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Integration;

use Joomla\Http\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Integration\RecaptchaVerifier;

if (!class_exists(Http::class)) {
    eval('namespace Joomla\\Http; class Http { public function post($url, $data, array $headers = [], $timeout = null) {} }');
}

final class RecaptchaVerifierTest extends TestCase
{
    public function testRejectsMissingCredentialsWithoutCallingGoogle(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::never())->method('post');
        $verifier = new RecaptchaVerifier($http);

        self::assertFalse($verifier->verify('', 'response', '127.0.0.1'));
        self::assertFalse($verifier->verify('secret', '', '127.0.0.1'));
    }

    public function testAcceptsSuccessfulGoogleVerification(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method('post')
            ->with(
                'https://www.google.com/recaptcha/api/siteverify',
                ['secret' => 'secret', 'response' => 'response', 'remoteip' => '127.0.0.1']
            )
            ->willReturn((object) ['body' => '{"success":true}', 'code' => 200]);

        self::assertTrue((new RecaptchaVerifier($http))->verify('secret', 'response', '127.0.0.1'));
    }

    /** @return iterable<string, array{int, string}> */
    public static function unsuccessfulVerificationProvider(): iterable
    {
        yield 'google rejects response' => [200, '{"success":false}'];
        yield 'google returns an error status' => [500, '{"success":true}'];
    }

    #[DataProvider('unsuccessfulVerificationProvider')]
    public function testRejectsUnsuccessfulGoogleVerification(int $status, string $body): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method('post')
            ->willReturn((object) ['body' => $body, 'code' => $status]);

        self::assertFalse((new RecaptchaVerifier($http))->verify('secret', 'response', '127.0.0.1'));
    }
}
