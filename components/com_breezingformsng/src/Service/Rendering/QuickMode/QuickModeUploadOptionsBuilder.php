<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeUploadOptionsBuilder
{
    /**
     * @param array<string, mixed> $data
     * @return array{extensions: string, maxFileSize: string}
     */
    public function build(array $data): array
    {
        $extensions = implode(',', explode(',', (string) ($data['allowedFileExtensions'] ?? '')));
        $bytes = $data['flashUploaderBytes'] ?? null;
        $maxFileSize = is_numeric($bytes) && $bytes > 0
            ? "max_file_size : '" . (int) $bytes . "',"
            : '';

        return [
            'extensions' => $extensions,
            'maxFileSize' => $maxFileSize,
        ];
    }
}
