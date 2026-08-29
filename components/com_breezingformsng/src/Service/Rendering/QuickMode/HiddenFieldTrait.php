<?php

declare(strict_types=1);

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL - EVH
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * First cut of the shared field-type Strategy layer: bfHidden's markup is
 * byte-for-byte identical across all four QuickMode renderers (Classic,
 * Bootstrap, Mobile, OnePage) - confirmed by their characterization test
 * snapshots before this trait existed. A trait (not a shared base class or
 * injected service) is the least invasive way to de-duplicate an identical
 * method body across four otherwise-unrelated renderer classes without
 * restructuring their constructors or instantiation.
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

\defined('_JEXEC') or die;

trait HiddenFieldTrait
{
    /**
     * @param array<string, mixed> $mdata
     */
    private function renderHiddenField(array $mdata): void
    {
        echo '<input class="ff_elem" type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
    }
}
