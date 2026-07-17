<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Upload;

\defined('_JEXEC') or die;

final class UploadResult
{
    private function __construct(
        public readonly string $path,
        public readonly string $serverPath,
        public readonly ?UploadError $error
    ) {
    }

    public static function success(string $path, string $serverPath): self
    {
        return new self($path, $serverPath, null);
    }

    public static function failure(UploadError $error): self
    {
        return new self('', '', $error);
    }

    public function isSuccessful(): bool
    {
        return $this->error === null;
    }
}
