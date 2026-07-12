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
 * every ancestor segment is a link back to that level, the current (last)
 * segment stays plain text.
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
            $parts[] = ($url !== null && $i !== $last)
                ? '<a href="' . Route::_($url) . '">' . $label . '</a>'
                : $label;
        }

        return implode(' / ', $parts);
    }
}
