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
use Joomla\CMS\Session\Session;

$pkg = $this->pkg;
?>
<form action="index.php?option=com_breezingformsng" method="post" name="adminForm" id="adminForm">
  <input type="hidden" name="view" value="menus">
  <input type="hidden" name="pkg" value="<?= htmlspecialchars($pkg); ?>">

  <?php if (!empty($this->packages)): ?>
  <div class="mb-3 d-flex align-items-center gap-2">
    <label class="form-label mb-0" for="pkg-select"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PACKAGE'); ?></label>
    <select id="pkg-select" class="form-select w-auto" onchange="this.form.pkg.value=this.value;Joomla.submitbutton('')">
      <option value=""<?= $pkg === '' ? ' selected' : ''; ?>><?= Text::_('JALL'); ?></option>
      <?php foreach ($this->packages as $p): ?>
        <option value="<?= htmlspecialchars($p); ?>"<?= $pkg === $p ? ' selected' : ''; ?>><?= htmlspecialchars($p); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th class="w-1 text-center">
          <input type="checkbox" class="form-check-input" onclick="Joomla.checkAll(this)" title="<?= Text::_('JGLOBAL_CHECK_ALL'); ?>">
        </th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_MENUS_TITLE'); ?></th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_MENUS_NAME'); ?></th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PARENT'); ?></th>
        <th><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PACKAGE'); ?></th>
        <th class="text-center w-10"><?= Text::_('JPUBLISHED'); ?></th>
        <th class="text-center w-10"><?= Text::_('JORDER'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($this->items)): ?>
        <tr><td colspan="7" class="text-center"><?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
      <?php else: ?>
        <?php $parentIndex = []; foreach ($this->items as $r) { if ($r->parent == 0) { $parentIndex[$r->id] = $r->title; } } ?>
        <?php foreach ($this->items as $i => $row): ?>
          <tr>
            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $row->id); ?></td>
            <td>
              <?= $row->parent > 0 ? '&mdash; ' : ''; ?>
              <a href="index.php?option=com_breezingformsng&view=menus&layout=edit&id=<?= (int) $row->id; ?>&pkg=<?= rawurlencode($pkg); ?>">
                <?= htmlspecialchars($row->title); ?>
              </a>
            </td>
            <td><?= htmlspecialchars($row->name); ?></td>
            <td><?= $row->parent > 0 ? htmlspecialchars($parentIndex[$row->parent] ?? '—') : '—'; ?></td>
            <td><?= htmlspecialchars($row->package); ?></td>
            <td class="text-center">
              <a href="#" onclick="bfTogglePublished(<?= (int) $row->id; ?>, 'menus', this); return false;"
                 title="<?= $row->published ? Text::_('JPUBLISHED') : Text::_('JUNPUBLISHED'); ?>">
                <span class="<?= $row->published ? 'icon-publish' : 'icon-unpublish'; ?>" aria-hidden="true"></span>
              </a>
            </td>
            <td class="text-center">
              <a href="index.php?option=com_breezingformsng&task=menus.orderup&cid[]=<?= (int) $row->id; ?>&pkg=<?= rawurlencode($pkg); ?>&<?= Session::getFormToken(); ?>=1"
                 title="<?= Text::_('JLIB_HTML_MOVE_UP'); ?>">
                <span class="icon-arrow-up-2" aria-hidden="true"></span>
              </a>
              <a href="index.php?option=com_breezingformsng&task=menus.orderdown&cid[]=<?= (int) $row->id; ?>&pkg=<?= rawurlencode($pkg); ?>&<?= Session::getFormToken(); ?>=1"
                 title="<?= Text::_('JLIB_HTML_MOVE_DOWN'); ?>">
                <span class="icon-arrow-down-2" aria-hidden="true"></span>
              </a>
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
// Web assets for this view are registered in Menus\HtmlView::display() —
// useScript() calls placed in the template body do not take effect here.
?>
