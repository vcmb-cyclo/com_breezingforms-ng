<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeUploadOptionsBuilder
{
    /**
     * @param array<string, mixed> $data
     * @return array{extensions: string, maxFileSize: string, multiSelection: string, runtimes: string}
     */
    public function build(array $data): array
    {
        $extensions = implode(',', explode(',', (string) ($data['allowedFileExtensions'] ?? '')));
        $bytes = $data['flashUploaderBytes'] ?? null;
        $maxFileSize = is_numeric($bytes) && $bytes > 0
            ? "max_file_size : '" . (int) $bytes . "',"
            : '';
        $multiSelection = !empty($data['flashUploaderMulti']) ? 'true' : 'false';
        $runtimes = (!empty($data['html5']) ? 'html5,' : '')
            . (!empty($data['flashUploader']) ? 'flash,' : '') . 'html4';

        return [
            'extensions' => $extensions,
            'maxFileSize' => $maxFileSize,
            'multiSelection' => $multiSelection,
            'runtimes' => $runtimes,
        ];
    }
}
