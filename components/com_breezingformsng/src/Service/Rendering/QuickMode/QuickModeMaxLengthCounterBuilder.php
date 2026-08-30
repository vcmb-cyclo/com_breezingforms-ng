<?php

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

final class QuickModeMaxLengthCounterBuilder
{
    public function build(int $elementId, int $maximum, string $charactersLeft): string
    {
        return ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter' . $elementId
            . '***>(' . $maximum . ' ' . $charactersLeft . ')</span>';
    }
}
