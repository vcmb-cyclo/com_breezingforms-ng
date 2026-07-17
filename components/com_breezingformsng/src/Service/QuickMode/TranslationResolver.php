<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\QuickMode;

\defined('_JEXEC') or die;

final class TranslationResolver
{
    /**
     * @param array<string, mixed> $data
     */
    public function formTitle(array $data, string $currentLanguage, string $defaultLanguage): string
    {
        $properties = $data['properties'] ?? [];
        $key = 'title_translation' . $this->translationTag($currentLanguage, $defaultLanguage);

        return isset($properties[$key]) ? (string) $properties[$key] : '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function field(
        array $data,
        string $property,
        string $fieldName,
        string $currentLanguage,
        string $defaultLanguage
    ): ?string {
        $translation = null;
        $this->findFieldTranslation(
            $data,
            $property,
            $fieldName,
            $this->translationTag($currentLanguage, $defaultLanguage),
            $translation
        );

        return $translation;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function findFieldTranslation(
        array $data,
        string $property,
        string $fieldName,
        string $languageTag,
        ?string &$translation
    ): void {
        $properties = $data['properties'] ?? null;

        if (isset($data['attributes']) && is_array($properties)
            && ($properties['type'] ?? null) === 'element'
            && trim((string) ($properties['bfName'] ?? '')) === trim($fieldName)
        ) {
            $key = $property . '_translation' . $languageTag;

            if (isset($properties[$key]) && $properties[$key] !== '') {
                $translation = (string) $properties[$key];
            }
        }

        $children = $data['children'] ?? [];

        if (!is_array($children)) {
            return;
        }

        foreach ($children as $child) {
            if (is_array($child)) {
                $this->findFieldTranslation($child, $property, $fieldName, $languageTag, $translation);
            }
        }
    }

    private function translationTag(string $currentLanguage, string $defaultLanguage): string
    {
        return $currentLanguage === $defaultLanguage ? 'zz-ZZ' : $currentLanguage;
    }
}
