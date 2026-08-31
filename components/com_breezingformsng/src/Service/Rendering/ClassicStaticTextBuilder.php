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

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the classic renderer markup for a static text/HTML element.
 */
final class ClassicStaticTextBuilder
{
    public function buildIcon(
        int $elementId,
        string $style,
        string $classAttribute,
        string $imageClassAttribute,
        string $source,
        string $label,
        string $hoverSource,
        string $eventAttributes,
        int $layout,
        bool $showBorder,
        int $width,
        int $height,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $wrapperAttributes = $showBorder
            ? ' onmouseout="ff_hideIconBorder(this);" onmouseover="ff_dispIconBorder(this);" style="padding:3px;' . $style . '"'
            : '  style="' . $style . '"';
        $swap = $hoverSource !== ''
            ? 'onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage(\'ff_img' . $elementId . '\',\'\',\'' . $hoverSource . '\',1);" '
            : '';
        $swap .= $eventAttributes;
        $dimensions = '';
        if ($width > 0) {
            $dimensions .= 'width="' . $width . '" ';
        }
        if ($height > 0) {
            $dimensions .= 'height="' . $height . '" ';
        }
        $image = '<img id="ff_img' . $elementId . '" src="' . $source . '" alt="" border="0" align="middle" '
            . $dimensions . $imageClassAttribute . '/>';

        $content = match ($layout) {
            1 => $indent . $indent . '<table id="ff_elem' . $elementId . '" cellpadding="1" cellspacing="0" border="0" ' . $swap . '>' . $newline
                . $indent . $indent . $indent . '<tr><td style="text-align:center;">' . $image . '</td></tr>' . $newline
                . $indent . $indent . $indent . '<tr><td style="text-align:center;">' . $label . '</td></tr>' . $newline
                . $indent . $indent . '</table>' . $newline,
            2 => $indent . $indent . '<table id="ff_elem' . $elementId . '" cellpadding="2" cellspacing="0" border="0" ' . $swap . '>' . $newline
                . $indent . $indent . $indent . '<tr><td style="text-align:center;">' . $label . '</td></tr>' . $newline
                . $indent . $indent . $indent . '<tr><td style="text-align:center;">' . $image . '</td></tr>' . $newline
                . $indent . $indent . '</table>' . $newline,
            3 => $indent . $indent . '<span id="ff_elem' . $elementId . '" ' . $swap . ' style="vertical-align:middle;">' . $newline
                . $indent . $indent . $indent . $label . ' &nbsp;' . $image . $newline
                . $indent . $indent . '</span>' . $newline,
            4 => $indent . $indent . '<span id="ff_elem' . $elementId . '" ' . $swap . ' style="vertical-align:middle;">' . $newline
                . $indent . $indent . $indent . $image . '&nbsp; ' . $label . $newline
                . $indent . $indent . '</span>' . $newline,
            0 => $indent . $indent . '<span id="ff_elem' . $elementId . '" ' . $swap . '>' . $newline
                . $indent . $indent . $indent . $image . $newline
                . $indent . $indent . '</span>' . $newline,
            default => $indent . $indent . '<span id="ff_elem' . $elementId . '" ' . $swap . ' style="vertical-align:middle;">' . $newline
                . $indent . $indent . $indent . $image . '&nbsp; ' . $label . $newline
                . $indent . $indent . '</span>' . $newline,
        };

        return $indent . '<div id="ff_div' . $elementId . '"' . $wrapperAttributes . $classAttribute . '>' . $newline
            . $content . $indent . '</div>' . $newline;
    }

    public function buildTooltip(
        int $elementId,
        string $style,
        string $classAttribute,
        string $imageClassAttribute,
        string $title,
        string $description,
        string $imageSource,
        int $imageType,
        string $siteRoot,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $tooltipTitle = '<strong>' . htmlspecialchars(strip_tags(trim($title)), ENT_QUOTES, 'UTF-8') . '</strong><br />' . str_replace(
            ["\n", "\r"],
            ['', ''],
            htmlentities(trim($description), ENT_QUOTES, 'UTF-8')
        );
        $tooltipClass = $classAttribute !== ''
            ? str_replace(' class="', ' class="hasTooltip ', $classAttribute)
            : ' class="hasTooltip"';
        $source = match ($imageType) {
            0 => $siteRoot . '/media/com_breezingformsng/images/site/tooltip.png',
            1 => $siteRoot . '/media/com_breezingformsng/images/site/warning.png',
            default => $imageSource,
        };

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $style . '" title="' . $tooltipTitle . '"'
            . $tooltipClass . '>' . $newline
            . $indent . $indent . '<img src="' . $source . '" alt="" border="0"' . $imageClassAttribute . '/>' . $newline
            . $indent . '</div>' . $newline;
    }

    public function buildImage(
        int $elementId,
        string $style,
        string $classAttribute,
        string $imageClassAttribute,
        string $source,
        string $alternative,
        int $width,
        int $height,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        $dimensions = '';
        if ($width > 0) {
            $dimensions .= 'width="' . $width . '" ';
        }
        if ($height > 0) {
            $dimensions .= 'height="' . $height . '" ';
        }

        return $indent . '<div id="ff_div' . $elementId . '" style="' . $style . '"' . $classAttribute . '>' . $newline
            . $indent . $indent . '<img id="ff_elem' . $elementId . '" src="' . $source . '"  alt="' . $alternative
            . '" border="0" ' . $dimensions . $imageClassAttribute . '/>' . $newline
            . $indent . '</div>' . $newline;
    }

    public function buildRectangle(
        int $elementId,
        string $style,
        string $classAttribute,
        string $border,
        string $backgroundColor,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        if ($border !== '') {
            $style .= 'border:' . $border . ';';
        }

        if ($backgroundColor !== '') {
            $style .= 'background-color:' . $backgroundColor . ';';
        }

        return $indent . '<div id="ff_div' . $elementId . '" style="font-size:0px;' . $style . '"'
            . $classAttribute . '></div>' . $newline;
    }

    public function build(
        int $elementId,
        string $style,
        string $classAttribute,
        string $content,
        string $indent = "\t",
        string $newline = "\n"
    ): string {
        return $indent . '<div id="ff_div' . $elementId . '" style="' . $style . '"' . $classAttribute . '>'
            . $content . '</div>' . $newline;
    }
}
