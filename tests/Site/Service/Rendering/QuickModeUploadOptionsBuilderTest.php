<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeUploadOptionsBuilder;

final class QuickModeUploadOptionsBuilderTest extends TestCase
{
    public function testNormalizesExtensionsAndPositiveMaximumSize(): void
    {
        self::assertSame(
            [
                'extensions' => 'jpg,png',
                'maxFileSize' => "max_file_size : '2048',",
                'multiSelection' => 'false',
                'runtimes' => 'html5,flash,html4',
            ],
            (new QuickModeUploadOptionsBuilder())->build([
                'allowedFileExtensions' => 'jpg,png',
                'flashUploaderBytes' => '2048',
                'html5' => true,
                'flashUploader' => true,
            ])
        );
    }

    public function testLeavesMaximumSizeEmptyWhenMissingOrNotPositive(): void
    {
        $builder = new QuickModeUploadOptionsBuilder();

        self::assertSame(
            ['extensions' => '', 'maxFileSize' => '', 'multiSelection' => 'false', 'runtimes' => 'html4'],
            $builder->build([])
        );
        self::assertSame(
            ['extensions' => 'pdf', 'maxFileSize' => '', 'multiSelection' => 'true', 'runtimes' => 'flash,html4'],
            $builder->build([
                'allowedFileExtensions' => 'pdf',
                'flashUploaderBytes' => 0,
                'flashUploaderMulti' => true,
                'flashUploader' => true,
            ])
        );
    }
}
