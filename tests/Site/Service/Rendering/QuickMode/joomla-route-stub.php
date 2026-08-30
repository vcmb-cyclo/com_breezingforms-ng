<?php

declare(strict_types=1);

/**
 * Minimal stub for Joomla\CMS\Router\Route, for renderer/RenderingEngine
 * characterization tests only. The real Route::_() resolves SEF settings
 * against the live application; here it just returns the route unchanged
 * so URLs embedded in captured output stay stable and legible.
 *
 * Shared across characterization test files; each one require_once's this
 * conditionally, so it only gets declared once.
 */

namespace Joomla\CMS\Router;

if (!class_exists(Route::class, false)) {
    final class Route
    {
        public static function _(string $route, bool $xhtml = true, ?int $ssl = null): string
        {
            return $route;
        }
    }
}
