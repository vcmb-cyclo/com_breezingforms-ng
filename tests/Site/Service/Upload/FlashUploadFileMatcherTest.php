<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Upload;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\FlashUploadFileMatcher;

final class FlashUploadFileMatcherTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/bfng-flash-match-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->directory));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        rmdir($this->directory);
    }

    public function testMatchesFieldTicketAndCompletedSuffix(): void
    {
        $matching = $this->directory . '/photo.jpg_attachment_ticket123_session_flashtmp';
        self::assertNotFalse(file_put_contents($matching, 'data'));
        self::assertNotFalse(file_put_contents(
            $this->directory . '/photo.jpg_other-ticket_ticket123_session_flashtmp',
            'data'
        ));
        self::assertNotFalse(file_put_contents(
            $this->directory . '/photo.jpg_attachment_ticket123_session_chunktmp',
            'data'
        ));

        $matches = (new FlashUploadFileMatcher())->find($this->directory, 'attachment', 'ticket123');

        self::assertSame([['path' => $matching, 'filename' => basename($matching)]], $matches);
    }

    public function testReturnsEmptyForMissingDirectory(): void
    {
        self::assertSame([], (new FlashUploadFileMatcher())->find($this->directory . '/missing', 'field', 'ticket'));
    }
}
