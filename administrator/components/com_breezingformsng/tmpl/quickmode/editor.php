<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$document = Factory::getApplication()->getDocument();

if (Factory::getApplication()->getInput()->getCmd('task') === 'quickmode.editor') {
    $document->getWebAssetManager()->addInlineScript('
    parent.jQuery(".modal-header .close").trigger("click");
    ');
}

$active_language_code = Factory::getApplication()->getInput()->getString('active_language_code', '');
if ($active_language_code != '') {
    $active_language_code = '_translation' . $active_language_code;
}

$document->getWebAssetManager()->useScript('com_breezingformsng.quickmode-editor');
$document->addScriptOptions('com_breezingformsng.quickmode-editor', [
    'langSuffix' => $active_language_code,
]);

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <input type="submit" class="btn btn-primary" value="<?php echo Text::_('JSAVE'); ?>" onclick="saveText();" /><br /><br />
    <div id="bfModalContainer" style="width:900px;">
        <?php echo $editor->display('bfEditor', '', 900, 300, 40, 20, false); ?>
    </div>
    <br /><input type="submit" class="btn btn-primary" value="<?php echo Text::_('JSAVE'); ?>" onclick="saveText();" />

    <input type="hidden" name="option" value="com_breezingformsng" />
    <input type="hidden" name="task" value="quickmode.editor" />
    <input type="hidden" name="tmpl" value="component" />
</form>
