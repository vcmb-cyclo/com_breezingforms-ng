<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

\defined('_JEXEC') or die;

final class JavascriptCompressor
{
    public function compress(string $javascript, int $breakAfter, string $lineEnding): string
    {
        $lines = explode("\n", str_replace("\r", '', $javascript));
        $code = '';
        $skip = '';
        $lineLength = 0;

        foreach ($lines as $line) {
            $length = strlen($line);
            $quote = '';
            $whitespace = false;
            $escape = false;

            for ($position = 0; $position < $length; $position++) {
                $character = substr($line, $position, 1);
                $pair = substr($line, $position, 2);

                if ($quote !== '') {
                    if ($escape) {
                        $code .= $character;
                        $lineLength++;
                        $escape = false;
                    } elseif ($character === '\\') {
                        $code .= $character;
                        $lineLength++;
                        $escape = true;
                    } elseif ($pair === $quote . $quote) {
                        $code .= $pair;
                        $lineLength += 2;
                        $position += 2;
                    } else {
                        $code .= $character;
                        $lineLength++;

                        if ($character === $quote) {
                            $quote = '';
                        }
                    }

                    continue;
                }

                if ($pair === $skip) {
                    $skip = '';
                    $position += 2;
                    continue;
                }

                if ($skip !== '') {
                    continue;
                }

                if ($pair === '/*') {
                    $skip = '*/';
                    $position += 2;
                    continue;
                }

                if ($pair === '//') {
                    break;
                }

                if ($character === ' ' || $character === "\t" || $character === "\n") {
                    if ($lineLength > 0) {
                        $whitespace = true;
                    }

                    continue;
                }

                if ($character === '"' || $character === "'") {
                    if ($whitespace && $this->isIdentifierCharacter(substr($code, -1))) {
                        $code .= ' ';
                        $lineLength++;
                    }

                    $whitespace = false;
                    $quote = $character;
                    $code .= $character;
                    $lineLength++;
                    continue;
                }

                if ($whitespace) {
                    if ($this->isIdentifierCharacter($character)
                        && $this->isIdentifierCharacter(substr($code, -1))
                    ) {
                        $code .= ' ';
                        $lineLength++;
                    }

                    $whitespace = false;
                }

                $code .= $character;
                $lineLength++;
            }

            if ($lineLength === 0) {
                continue;
            }

            if ($lineLength > $breakAfter || !str_contains(',;:{}=[(+-*%', substr($code, -1))) {
                $code .= $lineEnding;
                $lineLength = 0;
            }
        }

        if ($lineLength > 0) {
            $code .= $lineEnding;
        }

        return $code;
    }

    private function isIdentifierCharacter(string $character): bool
    {
        return $character === '_'
            || ($character >= '0' && $character <= '9')
            || ($character >= 'a' && $character <= 'z')
            || ($character >= 'A' && $character <= 'Z');
    }
}
