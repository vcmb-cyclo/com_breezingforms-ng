<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

\defined('_JEXEC') or die;

use Closure;
use Joomla\CMS\Uri\Uri;

final class ProcessorHeaderRenderer
{
    private const NEWLINE = "\r\n";

    public function __construct(private readonly JavascriptValueExporter $exporter)
    {
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function render(array $variables, bool $compress, Closure $compressor): string
    {
        $code = 'ff_processor = new Object();' . self::NEWLINE;

        foreach ($variables as $name => $value) {
            $code .= $this->exporter->exportVariable($name, $value);
        }

        return '<script type="text/javascript">' . self::NEWLINE
            . '<!--' . self::NEWLINE
            . ($compress ? $compressor($code) : $code)
            . '//-->' . self::NEWLINE
            . '</script>' . self::NEWLINE
            . '<script type="text/javascript" src="' . Uri::root(true)
            . '/media/com_breezingformsng/js/facileforms.js"></script>' . self::NEWLINE;
    }
}
