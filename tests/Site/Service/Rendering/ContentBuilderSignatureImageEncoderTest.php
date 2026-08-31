<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderSignatureImageEncoder;

final class ContentBuilderSignatureImageEncoderTest extends TestCase
{
    public function testEncodesExistingImageContents(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'bfng-signature-');
        self::assertNotFalse($path);
        self::assertSame(4, file_put_contents($path, 'data'));

        try {
            self::assertSame('ZGF0YQ==', (new ContentBuilderSignatureImageEncoder())->encode($path));
        } finally {
            unlink($path);
        }
    }

    public function testReturnsEmptyForMissingImage(): void
    {
        self::assertSame(
            '',
            (new ContentBuilderSignatureImageEncoder())->encode(
                sys_get_temp_dir() . '/bfng-missing-signature-' . bin2hex(random_bytes(4))
            )
        );
    }
}
