<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderSignatureFileResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderSignatureImageEncoder;

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

    public function testEncodesAnExistingSignatureImage(): void
    {
        $directory = sys_get_temp_dir() . '/bfng-signature-encoder-test/';
        mkdir($directory, 0777, true);
        $path = $directory . 'signature.png';
        file_put_contents($path, "signature\0bytes");

        try {
            self::assertSame(
                base64_encode("signature\0bytes"),
                (new ContentBuilderSignatureImageEncoder())->encode($path)
            );
        } finally {
            unlink($path);
            rmdir($directory);
        }
    }

    public function testReturnsAnEmptyEncodingWhenTheSignatureCannotBeRead(): void
    {
        self::assertSame(
            '',
            (new ContentBuilderSignatureImageEncoder())->encode(
                sys_get_temp_dir() . '/missing-bfng-signature.png'
            )
        );
    }
}
