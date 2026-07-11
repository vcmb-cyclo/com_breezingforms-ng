<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\Helpers\Bootstrap;
?>
                                    <div class="bfProperties" id="bfSectionProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TYPE')); ?>"
                                                    for="bfSectionType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_TYPE'); ?>
                                                </label>
                                                <select id="bfSectionType">
                                                    <option value="normal">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_NORMAL'); ?>
                                                    </option>
                                                    <option value="section">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_FIELDSET'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_DISPLAY_TYPE')); ?>"
                                                    for="bfSectionDisplayType">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_DISPLAY_TYPE'); ?>
                                                </label>
                                                <select id="bfSectionDisplayType">
                                                    <option value="inline">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_INLINE'); ?>
                                                    </option>
                                                    <option value="breaks">
                                                        <?php echo BFText::_('COM_BREEZINGFORMSNG_BREAKS'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TITLE')); ?>"
                                                    for="bfSectionTitle">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_TITLE'); ?>
                                                </label>
                                                <input type="text" value="" id="bfSectionTitle" />
                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_TITLE')); ?>"
                                                    for="bfSectionTitleTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_TITLE'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>
                                                <input type="text" value="" id="bfSectionTitleTrans" />
                                            </div>

                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_NAME')); ?>"
                                                    for="bfSectionName">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_NAME'); ?>
                                                </label>
                                                <input type="text" value="" id="bfSectionName" />
                                            </div>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_DESCRIPTION')); ?>"
                                                    for="bfSectionDescription">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_DESCRIPTION'); ?>
                                                </label>


                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesDescriptionModal',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&task=quickmode.editor",
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a
                                                    href="javascript:jQuery('#bfPropertiesDescriptionModal').modal('show');void(0);">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_DESCRIPTION'); ?>
                                                </a>

                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_SECTION_DESCRIPTION')); ?>"
                                                    for="bfSectionDescriptionTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_SECTION_DESCRIPTION'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>

                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesDescriptionModal2',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&task=quickmode.editor&active_language_code=" . $active_language_code,
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a
                                                    href="javascript:jQuery('#bfPropertiesDescriptionModal').modal('show');void(0);">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_DESCRIPTION'); ?>
                                                </a>


                                            </div>
                                        </fieldset>
                                    </div>
                                    <!-- SECTION PROPERTIES END -->

                                    <!-- ELEMENT PROPERTIES BEGIN -->
