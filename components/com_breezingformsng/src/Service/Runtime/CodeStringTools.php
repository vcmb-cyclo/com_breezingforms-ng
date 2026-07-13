<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

final class CodeStringTools
{
    public function trimInPlace(string &$code): bool
    {
        $length = strlen($code);

        if ($length === 0) {
            return false;
        }

        if (
            strpos(" \t\r\n", $code[0]) === false
            && strpos(" \t\r\n", $code[$length - 1]) === false
        ) {
            return true;
        }

        $code = trim($code);

        return $code !== '';
    }

    public function containsNonWhitespace(string $code): bool
    {
        return preg_match('/[^\s]+/si', $code) === 1;
    }
}
