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
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderFileSupportBuilder;

final class ContentBuilderFileSupportBuilderTest extends TestCase
{
    public function testDisplayNamePreservesEscapingAndHistoricalBreakMarkup(): void
    {
        $builder = new ContentBuilderFileSupportBuilder();
        self::assertSame('<br>.pdf', $builder->displayName('/tmp/<br>.pdf'));
        self::assertSame('&lt;script&gt;.txt', $builder->displayName('<script>.txt'));
    }

    public function testParseValuePreservesEmptyLinesAndCount(): void
    {
        self::assertSame(
            ['count' => 3, 'files' => ['one.pdf', '', 'two.pdf']],
            (new ContentBuilderFileSupportBuilder())->parseValue("one.pdf\r\n\ntwo.pdf")
        );
    }

    public function testResolveSignatureReturnsOnlyExistingFiles(): void
    {
        $directory = sys_get_temp_dir() . '/bfng-support-' . uniqid('', true) . '/';
        mkdir($directory);
        file_put_contents($directory . 'signature.png', 'data');

        try {
            $builder = new ContentBuilderFileSupportBuilder();
            self::assertSame($directory . 'signature.png', $builder->resolveSignature($directory, 'signature.png'));
            self::assertNull($builder->resolveSignature($directory, 'missing.png'));
            self::assertNull($builder->resolveSignature($directory, ''));
        } finally {
            unlink($directory . 'signature.png');
            rmdir($directory);
        }
    }
}
