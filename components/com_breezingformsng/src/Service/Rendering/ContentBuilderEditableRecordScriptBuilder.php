<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use Closure;

/** Builds the ContentBuilder editable-record hydration and upload scripts. */
final class ContentBuilderEditableRecordScriptBuilder
{
    public function __construct(
        private readonly Closure $cleanValue,
        private readonly Closure $wordWrap
    ) {
    }

    /**
     * @param iterable<object> $records
     * @param list<int|string> $nonEditableFields
     * @return array{contentBuilderScript: string, javascript: string}
     */
    public function build(
        iterable $records,
        array $nonEditableFields,
        bool $quickMode,
        int $formId,
        string $signatureDirectory
    ): array {
        $contentBuilderScript = '';
        $javascript = '';
        $flashUploadValidationAdded = false;

        foreach ($records as $entry) {
            if (in_array($entry->recElementId, $nonEditableFields)) {
                continue;
            }

            $recordValue = ($this->cleanValue)((string) $entry->recValue);

            switch ($entry->recType) {
                case 'File Upload':
                    if (!$quickMode) {
                        break;
                    }

                    if (!$flashUploadValidationAdded) {
                        $contentBuilderScript .= (new ContentBuilderFlashUploadValidationBuilder())->build();
                        $flashUploadValidationAdded = true;
                    }

                    $fileSupport = new ContentBuilderFileSupportBuilder();
                    $fileValue = $fileSupport->parseValue($recordValue);
                    $count = $fileValue['count'];
                    $contentBuilderScript .= '\n'
                        . '                                    cbFlashElemCnt["ff_elem' . $entry->recElementId . '"] = '
                        . $count . ";\n                                ";
                    $displayNames = [];
                    foreach ($fileValue['files'] as $file) {
                        if (trim($file)) {
                            $displayNames[] = $fileSupport->displayName(
                                ($this->wordWrap)($file, 150, '<br>', true)
                            );
                        }
                    }
                    $uploadControls = (new ContentBuilderFileUploadScriptBuilder())->build(
                        (int) $entry->recElementId,
                        (string) $entry->recName,
                        $count,
                        $displayNames
                    );
                    $javascript .= $uploadControls['deactivation'];
                    $javascript .= (new ContentBuilderFileHydrationScriptBuilder())->build(
                        (int) $entry->recElementId,
                        $uploadControls['html']
                    );
                    break;

                case 'Signature':
                    $signaturePath = (new ContentBuilderFileSupportBuilder())->resolveSignature(
                        $signatureDirectory,
                        $recordValue
                    );
                    if ($signaturePath !== null) {
                        $encoded = (new ContentBuilderSignatureImageEncoder())->encode($signaturePath);
                        $javascript .= (new ContentBuilderSignatureScriptBuilder())->build(
                            (string) $entry->recName,
                            (int) $entry->recElementId,
                            (string) $encoded
                        );
                    }
                    break;

                case 'Textarea':
                case 'Text':
                case 'Number Input':
                case 'Hidden Input':
                case 'Calendar':
                    $javascript .= (new ContentBuilderValueHydrationScriptBuilder())->build(
                        (string) $entry->recType,
                        (string) $entry->recName,
                        (int) $entry->recElementId,
                        $recordValue
                    );
                    break;

                case 'Checkbox':
                case 'Checkbox Group':
                    $javascript .= (new ContentBuilderChoiceHydrationScriptBuilder())->build(
                        'checkbox',
                        (string) $entry->recName,
                        $formId,
                        $recordValue
                    );
                    break;

                case 'Radio Button':
                case 'Radio Group':
                    $javascript .= (new ContentBuilderChoiceHydrationScriptBuilder())->build(
                        'radio',
                        (string) $entry->recName,
                        $formId,
                        $recordValue
                    );
                    break;

                case 'Select List':
                    $javascript .= (new ContentBuilderSelectHydrationScriptBuilder())->build(
                        (int) $entry->recElementId,
                        $recordValue
                    );
                    break;
            }
        }

        return [
            'contentBuilderScript' => $contentBuilderScript,
            'javascript' => $javascript,
        ];
    }
}
