<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Administrator\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RawCodeInputTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function codePayloadProvider(): iterable
    {
        yield 'piece code' => [
            'code',
            <<<'PHP'
<?php
$message = "outer 'inner' and \"nested\" quotes";
$query = 'name="O\'Reilly" & enabled=true';
return $message . "\n" . $query;
PHP,
        ];

        yield 'finalize code' => [
            'finalizeCode',
            <<<'PHP'
if ($status === "ok") {
    $result = ['html' => '<a title="A & B">Link</a>'];
}
PHP,
        ];
    }

    #[DataProvider('codePayloadProvider')]
    public function testRawInputPreservesUrlEncodedPhpCode(string $field, string $payload): void
    {
        $body = http_build_query([$field => $payload], '', '&', PHP_QUERY_RFC3986);
        parse_str($body, $parsedBody);

        self::assertSame($payload, $parsedBody[$field] ?? null);
    }
}
