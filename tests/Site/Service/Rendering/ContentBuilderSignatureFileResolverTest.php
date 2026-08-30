<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderSignatureFileResolver;

final class ContentBuilderSignatureFileResolverTest extends TestCase
{
    public function testResolvesAnExistingSignatureFile(): void
    {
        $directory = sys_get_temp_dir() . '/bfng-signature-test/';
        mkdir($directory, 0777, true);
        file_put_contents($directory . 'signature.png', 'signature');

        try {
            self::assertSame(
                $directory . 'signature.png',
                (new ContentBuilderSignatureFileResolver())->resolve($directory, 'signature.png')
            );
        } finally {
            unlink($directory . 'signature.png');
            rmdir($directory);
        }
    }

    public function testReturnsNullForEmptyOrMissingSignature(): void
    {
        $resolver = new ContentBuilderSignatureFileResolver();

        self::assertNull($resolver->resolve(sys_get_temp_dir() . '/', ''));
        self::assertNull($resolver->resolve(sys_get_temp_dir() . '/', 'missing-signature.png'));
    }
}
