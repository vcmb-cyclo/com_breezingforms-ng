<?php

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering\QuickMode;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeSubmitButtonBuilder;

final class QuickModeSubmitButtonBuilderTest extends TestCase
{
    public function testBuildsButtonWithHistoricalDuplicateTypeAttribute(): void
    {
        self::assertSame(
            '<button type="button" class="ff_elem btn" value="Send" type="submit" name="ff_nm_send[]" id="ff_elem19"><span>Send</span></button>' . "\n",
            (new QuickModeSubmitButtonBuilder())->build(
                'button', 'type="button" class="ff_elem btn"', 'value="Send" ', 'submit', 'send', 19, '<span>Send</span>'
            )
        );
    }

    public function testBuildsImageInputWithTrailingValue(): void
    {
        self::assertSame(
            '<input type="image" class="ff_elem" src="send.png" type="image" alt="" name="ff_nm_send[]" id="ff_elem20" value="Send"/>' . "\n",
            (new QuickModeSubmitButtonBuilder())->build(
                'input', 'type="image" class="ff_elem"', 'src="send.png" ', 'image', 'send', 20, '', ' alt=""', ' value="Send"'
            )
        );
    }
}
