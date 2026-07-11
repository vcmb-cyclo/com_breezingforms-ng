<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form action="index.php?option=com_breezingformsng&amp;act=integrate&amp;view=integrator" method="post" name="adminForm" id="adminForm">

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th class="w-1 text-center">
          <input type="checkbox" class="form-check-input" onclick="Joomla.checkAll(this)" title="<?= Text::_('JGLOBAL_CHECK_ALL'); ?>">
        </th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_INTEGRATOR_RULENAME'); ?></th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_TYPE'); ?></th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_FORM'); ?></th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_INTEGRATOR_TABLE'); ?></th>
        <th class="text-center w-10"><?= Text::_('JPUBLISHED'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($this->rules)): ?>
        <tr><td colspan="6" class="text-center"><?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
      <?php else: ?>
        <?php foreach ($this->rules as $i => $rule): ?>
          <tr>
            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $rule->id); ?></td>
            <td>
              <a href="index.php?option=com_breezingformsng&act=integrate&view=integrator&layout=edit&id=<?= (int) $rule->id; ?>">
                <?= htmlspecialchars($rule->name); ?>
              </a>
            </td>
            <td><?= htmlspecialchars($rule->type); ?></td>
            <td><?= htmlspecialchars($rule->form_name); ?></td>
            <td><?= htmlspecialchars($rule->reference_table); ?></td>
            <td class="text-center">
              <?php if ($rule->published): ?>
                <a href="index.php?option=com_breezingformsng&task=integrator.unpublish&publish_id=<?= (int) $rule->id; ?>&<?= \Joomla\CMS\Session\Session::getFormToken(); ?>=1"
                   class="tbody-icon active" title="<?= Text::_('JPUBLISHED'); ?>">
                  <span class="icon-publish" aria-hidden="true"></span>
                </a>
              <?php else: ?>
                <a href="index.php?option=com_breezingformsng&task=integrator.publish&publish_id=<?= (int) $rule->id; ?>&<?= \Joomla\CMS\Session\Session::getFormToken(); ?>=1"
                   class="tbody-icon" title="<?= Text::_('JUNPUBLISHED'); ?>">
                  <span class="icon-unpublish" aria-hidden="true"></span>
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="boxchecked" value="0">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<?php
$bfDocument = Factory::getApplication()->getDocument();
$bfDocument->getWebAssetManager()->useScript('com_breezingformsng.admin-form');
$bfDocument->addScriptOptions('com_breezingformsng.admin-form', ['confirmDeleteTask' => 'integrator.remove']);
Text::script('JGLOBAL_CONFIRM_DELETE');
?>
