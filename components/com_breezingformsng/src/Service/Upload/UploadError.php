<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Upload;

\defined('_JEXEC') or die;

enum UploadError: string
{
    case DirectoryMissing = 'directory_missing';
    case FileExists = 'file_exists';
    case MoveFailed = 'move_failed';
    case ChmodFailed = 'chmod_failed';
}
