<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\QuickMode\ElementFinder;
use Vcmb\Component\BreezingformsNG\Site\Service\QuickMode\TranslationResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\JavascriptValueExporter;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ProcessorHeaderRenderer;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\CodeStringTools;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RequestParameterParser;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\ImageResizer;

final class CoreServicesTest extends TestCase
{
    #[DataProvider('requestParameterProvider')]
    public function testParsesRequestParameters(string $input, array $expected): void
    {
        self::assertSame($expected, (new RequestParameterParser())->parse($input));
    }

    public static function requestParameterProvider(): array
    {
        return [
            'empty input' => ['', []],
            'plain parameters' => [' first = one &second=two ', ['first' => 'one', 'second' => 'two']],
            'html encoded separator' => ['first=one&amp;second=two', ['first' => 'one', 'second' => 'two']],
            'value containing equals sign' => ['query=a=b=c', ['query' => 'a=b=c']],
            'empty parameter names are ignored' => ['=ignored&& valid = value ', ['valid' => 'value']],
        ];
    }

    public function testTrimsCodeInPlaceAndReportsWhetherCodeRemains(): void
    {
        $tools = new CodeStringTools();
        $code = "  alert('ok');\n";

        self::assertTrue($tools->trimInPlace($code));
        self::assertSame("alert('ok');", $code);

        $whitespace = " \t\n";
        self::assertFalse($tools->trimInPlace($whitespace));
        self::assertSame('', $whitespace);
    }

    public function testFindsNestedElement(): void
    {
        $element = [
            'attributes' => ['id' => 'nested'],
            'properties' => ['type' => 'element', 'bfName' => 'email'],
        ];
        $node = ['children' => [['children' => [$element]]]];

        self::assertSame($element, (new ElementFinder())->find($node, 'email'));
        self::assertNull((new ElementFinder())->find($node, 'missing'));
    }

    public function testResolvesDefaultAndTranslatedFormTitles(): void
    {
        $resolver = new TranslationResolver();
        $data = ['properties' => [
            'title_translationzz-ZZ' => 'Default title',
            'title_translationfr-FR' => 'Titre français',
        ]];

        self::assertSame('Default title', $resolver->formTitle($data, 'en-GB', 'en-GB'));
        self::assertSame('Titre français', $resolver->formTitle($data, 'fr-FR', 'en-GB'));
    }

    public function testResolvesNestedFieldTranslation(): void
    {
        $resolver = new TranslationResolver();
        $data = ['children' => [[
            'attributes' => [],
            'properties' => [
                'type' => 'element',
                'bfName' => 'email',
                'label_translationfr-FR' => 'Adresse e-mail',
            ],
        ]]];

        self::assertSame('Adresse e-mail', $resolver->field($data, 'label', ' email ', 'fr-FR', 'en-GB'));
        self::assertNull($resolver->field($data, 'label', 'name', 'fr-FR', 'en-GB'));
    }

    public function testExportsJavascriptValuesAndVariables(): void
    {
        $exporter = new JavascriptValueExporter();

        self::assertSame('null', $exporter->exportValue(null));
        self::assertSame('true', $exporter->exportValue(true));
        self::assertSame('42', $exporter->exportValue(42));
        self::assertSame("'line\\n'+\r\n'\\'quote\\\\\\074'", $exporter->exportValue("line\n'quote\\<"));
        self::assertStringContainsString("'value'", $exporter->exportVariable('answer', 'value'));
        self::assertStringStartsWith("items = [\r\n", $exporter->exportVariable('items', ['first']));
    }

    public function testRendersProcessorHeaderUncompressedAndCompressed(): void
    {
        $renderer = new ProcessorHeaderRenderer(new JavascriptValueExporter());
        $variables = ['answer' => 42];

        $plain = $renderer->render($variables, false, static fn (string $code): string => $code);
        self::assertStringContainsString('ff_processor = new Object();', $plain);
        self::assertStringContainsString('answer = 42;', $plain);

        $compressed = $renderer->render($variables, true, static fn (string $code): string => 'compressed:' . $code);
        self::assertStringContainsString('compressed:ff_processor = new Object();', $compressed);
    }

    #[DataProvider('byteSizeProvider')]
    public function testParsesUploadByteSizes(string $value, int|float $expected): void
    {
        self::assertSame($expected, (new ImageResizer())->parseByteSize($value));
    }

    public static function byteSizeProvider(): array
    {
        return [
            'kilobytes' => ['2k', 2.0 * 1024],
            'megabytes with whitespace' => [' 1M ', 1048576.0],
            'gigabytes' => ['1g', 1024.0 * 1048576],
            'empty value uses safe default' => ['', 8 * 1048576],
            'unsupported unit uses safe default' => ['100b', 8 * 1048576],
        ];
    }
}
