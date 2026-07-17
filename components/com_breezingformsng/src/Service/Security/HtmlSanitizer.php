<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Security;

\defined('_JEXEC') or die;

use DOMDocument;
use DOMElement;

final class HtmlSanitizer
{
    private const FORBIDDEN_TAGS = ['script', 'style', 'iframe', 'applet', 'link'];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument();

        if (!@$document->loadHTML('<div>' . $html . '</div>')) {
            return strip_tags($html);
        }

        foreach (self::FORBIDDEN_TAGS as $tagName) {
            $nodes = $document->getElementsByTagName($tagName);

            while ($nodes->length > 0) {
                $node = $nodes->item(0);

                if ($node === null || $node->parentNode === null) {
                    break;
                }

                $node->parentNode->removeChild($node);
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);

        if ($body === null) {
            return strip_tags($html);
        }

        $result = new DOMDocument();

        foreach ($body->childNodes as $child) {
            $result->appendChild($result->importNode($child, true));
        }

        $container = $result->getElementsByTagName('div')->item(0);

        if (!$container instanceof DOMElement || $container->parentNode === null) {
            return strip_tags($html);
        }

        $container->parentNode->removeChild($container);

        while ($result->firstChild !== null) {
            $result->removeChild($result->firstChild);
        }

        while ($container->firstChild !== null) {
            $result->appendChild($container->firstChild);
        }

        return (string) $result->saveHTML();
    }
}
