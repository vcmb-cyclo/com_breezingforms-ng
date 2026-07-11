<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\Helpers\Bootstrap;
?>
                                    <div class="bfProperties" id="bfPageProperties" style="display:none">
                                        <br />
                                        <fieldset>
                                            <legend>
                                                <?php echo BFText::_('COM_BREEZINGFORMSNG_PAGE_PROPERTIES'); ?>
                                            </legend>
                                            <div class="bfPropertyWrap">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAGE_INTRO')); ?>"
                                                    for="bfPageIntro">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_PAGE_INTRO'); ?>
                                                </label>

                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesIntroModal',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&task=quickmode.editor",
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a href="javascript:jQuery('#bfPropertiesIntroModal').modal('show');void(0);"
                                                    data-toggle="modal">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_INTRO'); ?>
                                                </a>

                                            </div>

                                            <div class="bfPropertyWrap bfTrans">
                                                <label class="bfPropertyLabel hasTooltip"
                                                    title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_QM_PAGE_INTRO')); ?>"
                                                    for="bfPageIntroTrans">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_PAGE_INTRO'); ?>
                                                    <br /><em>(
                                                        <?php echo $active_language_code ?>)
                                                    </em>
                                                </label>


                                                <?php
                                                echo Bootstrap::renderModal(
                                                    'bfPropertiesIntroModal2',
                                                    array(
                                                        'url' => "index.php?option=com_breezingformsng&tmpl=component&task=quickmode.editor&active_language_code=" . $active_language_code,
                                                        'width' => '1024px',
                                                        'height' => '500px'
                                                    )
                                                );
                                                ?>

                                                <a href="javascript:jQuery('#bfPropertiesIntroModal2').modal('show');void(0);"
                                                    data-toggle="modal">
                                                    <?php echo BFText::_('COM_BREEZINGFORMSNG_EDIT_INTRO'); ?>
                                                </a>

                                            </div>
                                        </fieldset>
                                    </div>
                                    <!-- PAGE PROPERTIES END -->

                                    <!-- SECTION PROPERTIES BEGIN -->
