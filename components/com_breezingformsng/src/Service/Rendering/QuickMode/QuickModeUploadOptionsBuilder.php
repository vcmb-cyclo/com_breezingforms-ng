<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeUploadOptionsBuilder
{
    /**
     * @param array<string, mixed> $data
     * @return array{
     *     extensions: string,
     *     maxFileSize: string,
     *     maxBytes: string,
     *     multiSelection: string,
     *     runtimes: string,
     *     buttonWidth: string,
     *     buttonHeight: string
     * }
     */
    public function build(array $data): array
    {
        $extensions = implode(',', explode(',', (string) ($data['allowedFileExtensions'] ?? '')));
        $bytes = $data['flashUploaderBytes'] ?? null;
        $maxBytes = is_numeric($bytes) && $bytes > 0 ? (string) (int) $bytes : '0';
        $maxFileSize = $maxBytes !== '0' ? "max_file_size : '" . $maxBytes . "'," : '';
        $multiSelection = !empty($data['flashUploaderMulti']) ? 'true' : 'false';
        $runtimes = (!empty($data['html5']) ? 'html5,' : '')
            . (!empty($data['flashUploader']) ? 'flash,' : '') . 'html4';
        $buttonWidth = is_numeric($data['flashUploaderWidth'] ?? null) && $data['flashUploaderWidth'] > 0
            ? (string) (int) $data['flashUploaderWidth']
            : '64';
        $buttonHeight = is_numeric($data['flashUploaderHeight'] ?? null) && $data['flashUploaderHeight'] > 0
            ? (string) (int) $data['flashUploaderHeight']
            : '64';

        return [
            'extensions' => $extensions,
            'maxFileSize' => $maxFileSize,
            'maxBytes' => $maxBytes,
            'multiSelection' => $multiSelection,
            'runtimes' => $runtimes,
            'buttonWidth' => $buttonWidth,
            'buttonHeight' => $buttonHeight,
        ];
    }
}
