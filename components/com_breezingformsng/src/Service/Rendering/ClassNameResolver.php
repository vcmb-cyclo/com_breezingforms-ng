<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

\defined('_JEXEC') or die;

final class ClassNameResolver
{
    public function resolve(string $definition, int $template, string $suffix): string
    {
        $name = str_contains($definition, ';')
            ? (explode(';', $definition)[$template] ?? '')
            : $definition;
        $name = trim($name);

        return $name === '' ? '' : $name . $suffix;
    }
}
