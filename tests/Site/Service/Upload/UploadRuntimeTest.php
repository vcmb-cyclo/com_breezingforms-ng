<?php

declare(strict_types=1);

namespace Joomla\Input {
    if (!class_exists(Input::class)) {
        class Input
        {
        }
    }
}

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Upload {

use Joomla\Input\Input;
use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadRuntime;

final class UploadRuntimeTest extends TestCase
{
    public function testDelegatesByteSizeParsingAndImageDetection(): void
    {
        $runtime = new UploadRuntime(new Input());

        self::assertSame(2.0 * 1024, $runtime->parseByteSize('2k'));
        self::assertFalse($runtime->imageType('/tmp/missing-upload-image-' . bin2hex(random_bytes(4))));
    }

    public function testFindsQuickModeElementsThroughTheUploadRuntime(): void
    {
        $element = [
            'properties' => ['type' => 'element', 'bfName' => 'attachment'],
        ];
        $runtime = new UploadRuntime(new Input());

        self::assertSame(
            $element,
            $runtime->findQuickModeElement(['children' => [['children' => [$element]]]], 'attachment')
        );
        self::assertNull($runtime->findQuickModeElement([], 'missing'));
    }
}
}
