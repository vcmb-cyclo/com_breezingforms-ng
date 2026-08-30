<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/**
 * Builds the closing markup for the two supported form wrappers.
 */
final class FormClosingMarkupBuilder
{
    public function build(bool $legacyWrap, string $newline = "\r\n"): string
    {
        if ($legacyWrap) {
            return '</div></div></div><div class="bfPage-bl"><div class="bfPage-br"><div class="bfPage-b"></div></div></div></div><!-- form end -->' . $newline;
        }

        return '</div><!-- form end -->' . $newline;
    }
}
