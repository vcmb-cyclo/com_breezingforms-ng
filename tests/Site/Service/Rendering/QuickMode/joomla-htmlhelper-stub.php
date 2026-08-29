<?php

declare(strict_types=1);

/**
 * Minimal stub for Joomla\CMS\HTML\HTMLHelper, for renderer characterization
 * tests only. The real class dispatches to registered "behaviors" that push
 * JS/CSS assets onto the live WebAssetManager - none of that is observable
 * in a snapshot of process()'s echoed HTML, so a no-op is a faithful enough
 * double here. Shared across renderer characterization test files; each one
 * require_once's this conditionally, so it only gets declared once.
 */

namespace Joomla\CMS\HTML;

if (!class_exists(HTMLHelper::class, false)) {
    final class HTMLHelper
    {
        public static function _(string $key, mixed ...$args): mixed
        {
            return '';
        }
    }
}
