<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

final class FormDisplayContextResolver
{
    private const RUNMODE_FRONTEND = 0;
    private const RUNMODE_BACKEND = 1;
    private const RUNMODE_PREVIEW = 2;
    private const FORM_RUNMODE_ALL = 0;
    private const FORM_RUNMODE_FRONTEND = 1;
    private const FORM_RUNMODE_BACKEND = 2;

    public function resolve(
        int $runMode,
        bool $inFrame,
        int $formId,
        int $formRunMode,
        bool $published,
        int $previewMode,
        bool $gridEnabled,
        int $gridSize,
        string $siteUrl
    ): FormDisplayContext {
        $inline = 0;
        $template = 0;
        $htmlFormId = 'ff_form' . $formId;

        if ($runMode === self::RUNMODE_FRONTEND) {
            $homepage = $siteUrl;
        } elseif ($inFrame) {
            $homepage = $siteUrl . '/administrator/index.php?tmpl=component';
            $template = $formRunMode === self::FORM_RUNMODE_BACKEND ? 1 : 0;
        } else {
            $homepage = 'index.php?tmpl=component';
            $template = 1;

            if ($runMode === self::RUNMODE_PREVIEW) {
                $inline = 1;
                $htmlFormId = 'adminForm';
            }
        }

        $canRun = $published && match ($runMode) {
            self::RUNMODE_FRONTEND => $formRunMode === self::FORM_RUNMODE_ALL
                || $formRunMode === self::FORM_RUNMODE_FRONTEND,
            self::RUNMODE_BACKEND => $formRunMode === self::FORM_RUNMODE_ALL
                || $formRunMode === self::FORM_RUNMODE_BACKEND,
            default => true,
        };

        return new FormDisplayContext(
            $inline,
            $template,
            $htmlFormId,
            $homepage,
            $runMode === self::RUNMODE_PREVIEW && $previewMode > 0 && $gridEnabled && $gridSize > 1,
            $canRun
        );
    }
}
