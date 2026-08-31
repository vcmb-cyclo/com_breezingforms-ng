<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

/** Builds the shared payment method control used by QuickMode renderers. */
final class QuickModePaymentButtonBuilder
{
    public static function build(
        string $provider,
        string $fieldName,
        int $elementId,
        string $image,
        string $buttonValue,
        string $imageAlt,
        string $tabIndex,
        string $onblur,
        string $onchange,
        string $onfocus,
        string $onselect,
        string $readonly,
        bool $actionClick,
        string $actionFunctionName
    ): string {
        $type = $image === '' ? 'submit' : 'image';
        $value = $image === ''
            ? 'value="' . htmlspecialchars($buttonValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" '
            : '';
        $source = QuickModePaymentImageBuilder::build($image, $imageAlt);
        $onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'' . $provider . '\';';
        if ($actionClick) {
            $onclick .= $actionFunctionName . '(this,\'click\');';
        }
        $onclick .= '" ';
        $escapedType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedFieldName = htmlspecialchars($fieldName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<input class="ff_elem" ' . $value . $source . $tabIndex . $onclick
            . $onblur . $onchange . $onfocus . $onselect . $readonly
            . 'type="' . $escapedType . '" name="ff_nm_' . $escapedFieldName . '[]"'
            . ' id="ff_elem' . $elementId . '"/>' . "\n";
    }
}
