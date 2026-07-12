<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Renders the "BreezingForms NG / Section / Item" page title as a breadcrumb:
 * intermediate ancestors link back to their level; the component root and
 * the current (last) segment stay plain text.
 */
final class BreadcrumbHelper
{
    /**
     * @param array<int, array{label: string, url?: ?string}> $segments
     */
    public static function render(array $segments): string
    {
        $last = \count($segments) - 1;
        $parts = [];

        foreach ($segments as $i => $segment) {
            $label = htmlspecialchars((string) $segment['label'], ENT_QUOTES, 'UTF-8');
            $url = $segment['url'] ?? null;

            // Route::_() already HTML-encodes the URL (xhtml=true); do not re-escape it.
            $parts[] = ($url !== null && $i > 0 && $i !== $last)
                ? '<a href="' . Route::_($url) . '">' . $label . '</a>'
                : $label;
        }

        return implode(' / ', $parts);
    }
}
