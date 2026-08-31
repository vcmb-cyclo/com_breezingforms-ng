<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Callback;

use Joomla\CMS\Application\CMSApplication;
use Joomla\Http\Http;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\PayPalCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Support\RedirectHelper;

require_once __DIR__ . '/../Rendering/QuickMode/joomla-cmsapplication-stub.php';

if (!class_exists(Http::class)) {
    eval('namespace Joomla\\Http; class Http { public function post($url, $data, array $headers = [], $timeout = null) {} }');
}

if (!class_exists(CMSApplication::class)) {
    eval('namespace Joomla\\CMS\\Application; class CMSApplication {}');
}

if (!interface_exists(DatabaseInterface::class)) {
    eval('namespace Joomla\\Database; interface DatabaseInterface {}');
}

final class PayPalCallbackTest extends TestCase
{
    public function testRequestVerificationPostsToPayPalAndTrimsResponse(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method('post')
            ->with(
                'https://www.paypal.com/cgi-bin/webscr',
                'cmd=_notify-validate&txn_id=abc',
                ['Content-Type' => 'application/x-www-form-urlencoded']
            )
            ->willReturn((object) ['body' => " VERIFIED \n"]);

        self::assertSame(
            'VERIFIED',
            $this->invokeRequestVerification(
                $http,
                'https://www.paypal.com',
                'cmd=_notify-validate&txn_id=abc'
            )
        );
    }

    public function testRequestVerificationReturnsEmptyStringWhenHttpFails(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method('post')
            ->willThrowException(new \RuntimeException('network failure'));

        self::assertSame(
            '',
            $this->invokeRequestVerification($http, 'https://www.paypal.com', 'payload')
        );
    }

    private function invokeRequestVerification(Http $http, string $paypalUrl, string $body): string
    {
        $application = new CMSApplication();
        $callback = new PayPalCallback(
            $application,
            new class implements DatabaseInterface {
            },
            new RedirectHelper($application),
            $http
        );

        return (string) (new ReflectionMethod($callback, 'requestVerification'))->invoke(
            $callback,
            $paypalUrl,
            $body
        );
    }
}
