<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class JavascriptValueExporter
{
    private const NEWLINE = "\r\n";

    public function exportValue(mixed $value, string $indent = ''): string
    {
        if ($value === null) {
            return $indent . 'null';
        }

        if (\is_bool($value)) {
            return $indent . ($value ? 'true' : 'false');
        }

        if (\is_numeric($value)) {
            return $indent . $value;
        }

        if (\is_string($value)) {
            return $indent . "'" . str_replace(
                ["\\", "'", "\r", '<', "\n"],
                ["\\\\", "\\'", '\\r', '\\074', "\\n'+" . self::NEWLINE . $indent . "'"],
                $value
            ) . "'";
        }

        if (\is_array($value)) {
            $items = [];

            foreach ($value as $item) {
                $items[] = $this->exportValue($item, $indent . "\t");
            }

            return $indent . '[' . self::NEWLINE
                . implode(',' . self::NEWLINE, $items)
                . self::NEWLINE . $indent . ']';
        }

        if (\is_object($value)) {
            $properties = [];

            foreach (get_object_vars($value) as $key => $property) {
                $properties[] = $indent . $key . ':' . self::NEWLINE
                    . $this->exportValue($property, $indent . "\t");
            }

            return $indent . '{' . self::NEWLINE
                . implode(',' . self::NEWLINE, $properties)
                . self::NEWLINE . $indent . '}';
        }

        if (\is_resource($value)) {
            return $indent . "'" . Text::_('COM_BREEZINGFORMSNG_PROCESS_RESOURCE') . "'";
        }

        return $indent . "'" . Text::_('COM_BREEZINGFORMSNG_PROCESS_UNKNOWN') . "'";
    }

    public function exportVariable(string $name, mixed $value): string
    {
        return $name . ' = ' . $this->exportValue($value) . ';' . self::NEWLINE;
    }
}
