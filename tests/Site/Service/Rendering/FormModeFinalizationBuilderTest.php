<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\FormModeFinalizationBuilder;

final class FormModeFinalizationBuilderTest extends TestCase
{
    public function testFrontendKeepsHistoricalFieldOrder(): void
    {
        self::assertSame(
            "contextsubmissiontokenoptionaladditionaltechnicalrouting</form>\n",
            (new FormModeFinalizationBuilder())->frontend(
                'context',
                'submission',
                'token',
                'optional',
                'additional',
                'technical',
                'routing',
                "\n"
            )
        );
    }

    public function testBackendKeepsSubmissionBeforeContext(): void
    {
        self::assertSame(
            "submissiontokencontextoptionaltechnicalrouting</form>\n",
            (new FormModeFinalizationBuilder())->backend(
                'submission',
                'token',
                'context',
                'optional',
                'technical',
                'routing',
                "\n"
            )
        );
    }

    public function testPreviewReturnsNothingOutsideAnIframe(): void
    {
        $builder = new FormModeFinalizationBuilder();

        self::assertSame('', $builder->preview(
            false,
            'submission',
            'token',
            'context',
            'optional',
            'technical',
            'routing',
            "\n"
        ));

        self::assertSame(
            "submissiontokencontextoptionaltechnicalrouting</form>\n",
            $builder->preview(
                true,
                'submission',
                'token',
                'context',
                'optional',
                'technical',
                'routing',
                "\n"
            )
        );
    }
}
