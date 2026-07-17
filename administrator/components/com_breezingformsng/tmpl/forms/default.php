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
use Joomla\CMS\Pagination\Pagination;

$pkg = $this->pkg;

$pagination = new Pagination($this->total, $this->limitStart, $this->limit);
?>
<form action="index.php?option=com_breezingformsng&amp;act=manageforms&amp;view=forms" method="post" name="adminForm" id="adminForm">
  <input type="hidden" name="view" value="forms">

  <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">

    <?php if (!empty($this->packages)): ?>
    <select class="form-select w-auto" name="pkg" onchange="this.form.submit()">
      <option value=""<?= $pkg === '' ? ' selected' : ''; ?>><?= Text::_('JALL'); ?></option>
      <?php foreach ($this->packages as $p): ?>
        <option value="<?= htmlspecialchars($p); ?>"<?= $pkg === $p ? ' selected' : ''; ?>><?= htmlspecialchars($p); ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <select class="form-select w-auto" name="filter_state" onchange="this.form.submit()">
      <option value=""<?= $this->filterState === '' ? ' selected' : ''; ?>><?= Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
      <option value="P"<?= $this->filterState === 'P' ? ' selected' : ''; ?>><?= Text::_('JPUBLISHED'); ?></option>
      <option value="U"<?= $this->filterState === 'U' ? ' selected' : ''; ?>><?= Text::_('JUNPUBLISHED'); ?></option>
    </select>

    <div class="input-group w-auto">
      <input type="text" class="form-control" name="search"
             value="<?= htmlspecialchars($this->search); ?>"
             placeholder="<?= Text::_('JSEARCH'); ?>">
      <button type="submit" class="btn btn-primary"><?= Text::_('JSEARCH'); ?></button>
      <?php if ($this->search !== ''): ?>
        <a href="index.php?option=com_breezingformsng&act=manageforms&view=forms&search=&pkg=<?= rawurlencode($pkg); ?>"
           class="btn btn-secondary"><?= Text::_('JSEARCH_RESET'); ?></a>
      <?php endif; ?>
    </div>

  </div>

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th class="w-1 text-center">
          <input type="checkbox" class="form-check-input" onclick="Joomla.checkAll(this)" title="<?= Text::_('JGLOBAL_CHECK_ALL'); ?>">
        </th>
        <th><?= HTMLHelper::_('grid.sort', Text::_('COM_BREEZINGFORMSNG_FORMS_TITLE'), 'title', $this->listDirn, $this->listOrder); ?></th>
        <th><?= HTMLHelper::_('grid.sort', Text::_('COM_BREEZINGFORMSNG_FORMS_NAME'), 'name', $this->listDirn, $this->listOrder); ?></th>
        <th class="text-center"><?= HTMLHelper::_('grid.sort', Text::_('COM_BREEZINGFORMSNG_FORMS_PAGES'), 'pages', $this->listDirn, $this->listOrder); ?></th>
        <th><?= HTMLHelper::_('grid.sort', Text::_('COM_BREEZINGFORMSNG_FORMS_MODIFIED'), 'modified', $this->listDirn, $this->listOrder); ?></th>
        <th class="text-center w-10"><?= HTMLHelper::_('grid.sort', Text::_('JPUBLISHED'), 'published', $this->listDirn, $this->listOrder); ?></th>
        <th class="text-center w-10"><?= HTMLHelper::_('grid.sort', Text::_('JORDER'), 'ordering', $this->listDirn, $this->listOrder); ?></th>
        <th class="text-center"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_ACTIONS'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($this->items)): ?>
        <tr><td colspan="8" class="text-center"><?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
      <?php else: ?>
        <?php foreach ($this->items as $i => $form): ?>
          <tr>
            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $form->id); ?></td>
            <td>
              <a href="index.php?option=com_breezingformsng&task=quickmode.display&form=<?= (int) $form->id; ?>&pkg=<?= rawurlencode($pkg); ?>">
                <?= htmlspecialchars($form->title); ?>
              </a>
            </td>
            <td><?= htmlspecialchars($form->name); ?></td>
            <td class="text-center"><?= (int) $form->pages; ?></td>
            <td><?= $form->modified ? htmlspecialchars(substr((string) $form->modified, 0, 10)) : '—'; ?></td>
            <td class="text-center">
              <a href="#" onclick="bfTogglePublished(<?= (int) $form->id; ?>, 'forms', this); return false;"
                 title="<?= $form->published ? Text::_('JPUBLISHED') : Text::_('JUNPUBLISHED'); ?>">
                <span class="<?= $form->published ? 'icon-publish' : 'icon-unpublish'; ?>" aria-hidden="true"></span>
              </a>
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-link p-0 border-0" title="<?= Text::_('JLIB_HTML_MOVE_UP'); ?>"
                      onclick="this.form.action_id.value='<?= (int) $form->id; ?>';Joomla.submitbutton('forms.orderup');">
                <span class="icon-arrow-up-2" aria-hidden="true"></span>
              </button>
              <button type="button" class="btn btn-link p-0 border-0" title="<?= Text::_('JLIB_HTML_MOVE_DOWN'); ?>"
                      onclick="this.form.action_id.value='<?= (int) $form->id; ?>';Joomla.submitbutton('forms.orderdown');">
                <span class="icon-arrow-down-2" aria-hidden="true"></span>
              </button>
            </td>
            <td class="text-center">
              <a href="index.php?option=com_breezingformsng&task=quickmode.display&form=<?= (int) $form->id; ?>&pkg=<?= rawurlencode($pkg); ?>"
                 class="btn btn-sm btn-outline-secondary" title="<?= Text::_('COM_BREEZINGFORMSNG_FORMS_OPEN_EDITOR'); ?>">
                <span class="icon-edit" aria-hidden="true"></span>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?= $pagination->getListFooter(); ?>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="filter_order" value="<?= htmlspecialchars($this->listOrder); ?>">
  <input type="hidden" name="filter_order_Dir" value="<?= htmlspecialchars($this->listDirn); ?>">
  <input type="hidden" name="pkg" value="<?= htmlspecialchars($pkg); ?>">
  <input type="hidden" name="boxchecked" value="0">
  <input type="hidden" name="action_id" value="0">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<?php
// Web assets for this view are registered in Forms\HtmlView::display() —
// useScript() calls placed in the template body do not take effect here.
?>
