<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode\QuickModeEditorValueBuilder;

final class QuickModeEditorValueBuilderTest extends TestCase
{
    public function testBuildsEditorValueExpression(): void
    {
        self::assertSame(
            'Joomla.editors.instances["editor-12"].getValue()',
            QuickModeEditorValueBuilder::build('editor-12')
        );
    }
}
