<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Interprets the classic Query List presentation settings.
 */
final class ClassicQueryListSettingsBuilder
{
    /**
     * @param callable(string): string $classResolver
     * @return array{tableAttributes: string, headerClass: string, oddClass: string, evenClass: string, footerClass: string, footerCellClass: string, pageNavigation: int}
     */
    public function build(string $settingsText, int $width, callable $classResolver): array
    {
        $settings = explode("\n", $settingsText);
        foreach ($settings as &$setting) {
            $setting = trim($setting);
        }
        unset($setting);

        $tableAttributes = '';
        if (($settings[0] ?? '') !== '') {
            $tableAttributes .= ' border="' . $settings[0] . '"';
        }
        if (($settings[1] ?? '') !== '') {
            $tableAttributes .= ' cellspacing="' . $settings[1] . '"';
        }
        if (($settings[2] ?? '') !== '') {
            $tableAttributes .= ' cellpadding="' . $settings[2] . '"';
        }
        if ($width > 0) {
            $tableAttributes .= ' width="100%"';
        }

        return [
            'tableAttributes' => $tableAttributes,
            'headerClass' => $this->classAttribute($settings[3] ?? '', $classResolver),
            'oddClass' => $this->classAttribute($settings[4] ?? '', $classResolver),
            'evenClass' => $this->classAttribute($settings[5] ?? '', $classResolver),
            'footerClass' => $this->classAttribute($settings[6] ?? '', $classResolver),
            'footerCellClass' => $this->classAttribute($settings[7] ?? '', $classResolver),
            'pageNavigation' => (int) (($settings[8] ?? '') !== '' ? $settings[8] : 1),
        ];
    }

    /**
     * @param callable(string): string $classResolver
     */
    private function classAttribute(string $className, callable $classResolver): string
    {
        return $className === '' ? '' : ' class="' . $classResolver($className) . '"';
    }
}
