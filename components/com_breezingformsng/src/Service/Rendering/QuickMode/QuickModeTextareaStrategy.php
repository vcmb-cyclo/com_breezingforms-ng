<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/**
 * Builds the shared plain QuickMode textarea control.
 *
 * Theme renderers keep their envelopes and editor integration; this strategy
 * owns the repeated textarea metadata and sizing rules.
 */
final class QuickModeTextareaStrategy
{
    /**
     * @param array<string, mixed> $field
     */
    public function build(
        array $field,
        string $languageTag,
        string $class,
        string $attributes,
        string $beforeClass = '',
        bool $bootstrapSizing = false
    ): string {
        $field = $this->translatedField($field, $languageTag);
        $size = $this->size($field, $bootstrapSizing);
        $onkeyup = $this->maxLengthHandler($field);

        return (new QuickModeTextareaBuilder())->build(
            $class,
            (string) $field['bfName'],
            (string) ($field['value'] ?? ''),
            (int) $field['dbId'],
            $onkeyup . $size . $attributes,
            (string) ($field['placeholder'] ?? ''),
            $beforeClass
        );
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    private function translatedField(array $field, string $languageTag): array
    {
        foreach (['value', 'placeholder'] as $key) {
            $translationKey = $key . '_translation' . $languageTag;
            if (!empty($field[$translationKey])) {
                $field[$key] = $field[$translationKey];
            }
        }

        return $field;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function size(array $field, bool $bootstrapSizing): string
    {
        $width = $field['width'] ?? '';
        $height = $field['height'] ?? '';
        $size = '';

        if ($width !== '') {
            $width = 'width:' . htmlentities(strip_tags((string) $width));
            if ($bootstrapSizing) {
                $width .= ' !important; min-width:'
                    . htmlentities(strip_tags((string) $field['width'])) . ' !important;';
            } else {
                $width .= ';';
            }
        }

        if ($height !== '') {
            $height = 'height:' . htmlentities(strip_tags((string) $height)) . ';';
        }

        if ($width !== '' || $height !== '') {
            $size = 'style="' . $width . $height . '" ';
        }

        return $size;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function maxLengthHandler(array $field): string
    {
        if (!isset($field['maxlength']) || $field['maxlength'] <= 0) {
            return '';
        }

        return 'onkeyup="bfCheckMaxlength(' . intval($field['dbId']) . ', ' . intval($field['maxlength']) . ', '
            . (!empty($field['showMaxlengthCounter']) ? 'true' : 'false') . ')" ';
    }
}
