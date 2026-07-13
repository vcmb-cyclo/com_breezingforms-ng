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

$listOrder  = $this->listOrder;
$listDirn   = $this->listDirn;
$total      = $this->total;
$limitStart = $this->limitStart;
$limit      = $this->limit;
$formSelection = $this->formSelection;
$searchTerm = $this->searchTerm;

$totalPages  = ($limit > 0) ? (int) ceil($total / $limit) : 1;
$currentPage = ($limit > 0) ? (int) floor($limitStart / $limit) : 0;

$sortIcon = function (string $col) use ($listOrder, $listDirn): string {
    if ($listOrder !== $col) {
        return '';
    }
    return $listDirn === 'asc'
        ? ' <span class="icon-arrow-up" aria-hidden="true"></span>'
        : ' <span class="icon-arrow-down" aria-hidden="true"></span>';
};

$sortUrl = function (string $col) use ($listOrder, $listDirn, $formSelection, $searchTerm): string {
    $dir = ($listOrder === $col && $listDirn === 'asc') ? 'desc' : 'asc';
    return 'index.php?option=com_breezingformsng&act=managerecs&view=records'
        . '&filter_order=' . rawurlencode($col)
        . '&filter_order_Dir=' . $dir
        . '&form_selection=' . $formSelection
        . ($searchTerm !== '' ? '&searchterm=' . rawurlencode($searchTerm) : '')
        . '&limitstart=0';
};

$pageUrl = function (int $start) use ($listOrder, $listDirn, $formSelection, $searchTerm): string {
    return 'index.php?option=com_breezingformsng&act=managerecs&view=records'
        . '&filter_order=' . rawurlencode($listOrder)
        . '&filter_order_Dir=' . $listDirn
        . '&form_selection=' . $formSelection
        . ($searchTerm !== '' ? '&searchterm=' . rawurlencode($searchTerm) : '')
        . '&limitstart=' . $start;
};

$headerTitle = static fn (string $key): string => htmlspecialchars(Text::_($key), ENT_QUOTES, 'UTF-8');
?>
<form action="index.php?option=com_breezingformsng&amp;act=managerecs&amp;view=records" method="post" name="adminForm" id="adminForm">

  <div class="row mb-3">
    <div class="col-md-4">
      <select name="form_selection" id="form_selection" class="form-select" onchange="this.form.limitstart.value=0;this.form.submit();">
        <option value="0"><?= Text::_('COM_BREEZINGFORMSNG_ALL_FORMS'); ?></option>
        <?php foreach ($this->forms as $form): ?>
          <option value="<?= (int) $form['id']; ?>" <?= ($this->formSelection == $form['id']) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($form['title']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <div class="input-group">
        <input type="text" name="searchterm" class="form-control" placeholder="<?= Text::_('JSEARCH_FILTER'); ?>" value="<?= htmlspecialchars($this->searchTerm); ?>">
        <button type="submit" class="btn btn-primary" onclick="this.form.limitstart.value=0;" title="<?= $headerTitle('JSEARCH_FILTER_SUBMIT'); ?>"><?= Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
        <?php if ($this->searchTerm !== ''): ?>
          <a href="index.php?option=com_breezingformsng&act=managerecs&view=records&form_selection=<?= $this->formSelection; ?>" class="btn btn-secondary" title="<?= $headerTitle('JSEARCH_FILTER_CLEAR'); ?>"><?= Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-4 text-end">
      <span class="badge bg-secondary"><?= (int) $total; ?> <?= Text::_('COM_BREEZINGFORMSNG_RECORDS'); ?></span>
    </div>
  </div>

  <table class="table table-striped table-hover" id="recordList">
    <thead>
      <tr>
        <th class="w-1 text-center"><input type="checkbox" class="form-check-input" onclick="Joomla.checkAll(this)" title="<?= Text::_('JGLOBAL_CHECK_ALL'); ?>"></th>
        <th title="<?= $headerTitle('COM_BREEZINGFORMSNG_ID_DESC'); ?>"><a href="<?= $sortUrl('records.id'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_ID_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_ID'); ?><?= $sortIcon('records.id'); ?></a></th>
        <th title="<?= $headerTitle('COM_BREEZINGFORMSNG_RECORD_FORM_DESC'); ?>"><a href="<?= $sortUrl('forms.title'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_RECORD_FORM_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_FORM'); ?><?= $sortIcon('forms.title'); ?></a></th>
        <th title="<?= $headerTitle('COM_BREEZINGFORMSNG_IP_DESC'); ?>"><a href="<?= $sortUrl('records.ip'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_IP_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_IP'); ?><?= $sortIcon('records.ip'); ?></a></th>
        <th title="<?= $headerTitle('COM_BREEZINGFORMSNG_USER_DESC'); ?>"><a href="<?= $sortUrl('records.username'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_USER_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_USER'); ?><?= $sortIcon('records.username'); ?></a></th>
        <th class="text-center" title="<?= $headerTitle('COM_BREEZINGFORMSNG_VIEWED_DESC'); ?>"><a href="<?= $sortUrl('records.viewed'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_VIEWED_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_VIEWED'); ?><?= $sortIcon('records.viewed'); ?></a></th>
        <th class="text-center" title="<?= $headerTitle('COM_BREEZINGFORMSNG_EXPORTED_DESC'); ?>"><a href="<?= $sortUrl('records.exported'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_EXPORTED_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_EXPORTED'); ?><?= $sortIcon('records.exported'); ?></a></th>
        <th class="text-center" title="<?= $headerTitle('COM_BREEZINGFORMSNG_ARCHIVED_DESC'); ?>"><a href="<?= $sortUrl('records.archived'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_ARCHIVED_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_ARCHIVED'); ?><?= $sortIcon('records.archived'); ?></a></th>
        <th title="<?= $headerTitle('COM_BREEZINGFORMSNG_ACTIONS_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_ACTIONS'); ?></th>
        <th title="<?= $headerTitle('COM_BREEZINGFORMSNG_SUBMITTED_DESC'); ?>"><a href="<?= $sortUrl('records.submitted'); ?>" title="<?= $headerTitle('COM_BREEZINGFORMSNG_SUBMITTED_DESC'); ?>"><?= Text::_('COM_BREEZINGFORMSNG_SUBMITTED'); ?><?= $sortIcon('records.submitted'); ?></a></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($this->records)): ?>
        <tr><td colspan="10" class="text-center"><?= Text::_('COM_BREEZINGFORMSNG_NO_RECORDS_FOUND'); ?></td></tr>
      <?php else: ?>
        <?php foreach ($this->records as $i => $rec): ?>
          <?php $recId = (int) $rec['id']; ?>
          <tr>
            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $recId); ?></td>
            <td><?= $recId; ?></td>
            <td>
              <a href="index.php?option=com_breezingformsng&act=managerecs&view=records&layout=edit&record_id=<?= $recId; ?>&form_selection=<?= $this->formSelection; ?>" title="<?= $headerTitle('JACTION_EDIT'); ?>">
                <?= htmlspecialchars((string) $rec['form_title']); ?>
              </a>
            </td>
            <td><?= htmlspecialchars((string) $rec['ip']); ?></td>
            <td><?= htmlspecialchars((string) ($rec['user_full_name'] ?: $rec['username'])); ?></td>
            <td class="text-center">
              <a href="#" onclick="bfToggleFlag(<?= $recId; ?>, 'bfrecord_viewed', this); return false;" title="<?= Text::_('COM_BREEZINGFORMSNG_TOGGLE_VIEWED'); ?>">
                <span class="<?= $rec['viewed'] ? 'icon-check text-success' : 'icon-times text-danger'; ?>"></span>
              </a>
            </td>
            <td class="text-center">
              <a href="#" onclick="bfToggleFlag(<?= $recId; ?>, 'bfrecord_exported', this); return false;" title="<?= Text::_('COM_BREEZINGFORMSNG_TOGGLE_EXPORTED'); ?>">
                <span class="<?= $rec['exported'] ? 'icon-check text-success' : 'icon-times text-danger'; ?>"></span>
              </a>
            </td>
            <td class="text-center">
              <a href="#" onclick="bfToggleFlag(<?= $recId; ?>, 'bfrecord_archived', this); return false;" title="<?= Text::_('COM_BREEZINGFORMSNG_TOGGLE_ARCHIVED'); ?>">
                <span class="<?= $rec['archived'] ? 'icon-check text-success' : 'icon-times text-danger'; ?>"></span>
              </a>
            </td>
            <td>
              <a class="btn btn-sm btn-secondary" href="index.php?option=com_breezingformsng&act=managerecs&view=records&layout=edit&record_id=<?= $recId; ?>&form_selection=<?= $this->formSelection; ?>" title="<?= $headerTitle('JACTION_EDIT'); ?>" aria-label="<?= $headerTitle('JACTION_EDIT'); ?>">
                <span class="icon-edit" aria-hidden="true"></span>
              </a>
            </td>
            <td><?= htmlspecialchars((string) $rec['submitted']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($totalPages > 1): ?>
  <nav aria-label="<?= Text::_('JPAGINATION'); ?>">
    <ul class="pagination justify-content-center">
      <?php if ($currentPage > 0): ?>
        <li class="page-item"><a class="page-link" href="<?= $pageUrl(0); ?>">&laquo;</a></li>
        <li class="page-item"><a class="page-link" href="<?= $pageUrl(($currentPage - 1) * $limit); ?>">&lsaquo;</a></li>
      <?php endif; ?>
      <?php
      $startPage = max(0, $currentPage - 4);
      $endPage   = min($totalPages - 1, $currentPage + 4);
      for ($p = $startPage; $p <= $endPage; $p++):
      ?>
        <li class="page-item <?= ($p === $currentPage) ? 'active' : ''; ?>">
          <a class="page-link" href="<?= $pageUrl($p * $limit); ?>"><?= $p + 1; ?></a>
        </li>
      <?php endfor; ?>
      <?php if ($currentPage < $totalPages - 1): ?>
        <li class="page-item"><a class="page-link" href="<?= $pageUrl(($currentPage + 1) * $limit); ?>">&rsaquo;</a></li>
        <li class="page-item"><a class="page-link" href="<?= $pageUrl(($totalPages - 1) * $limit); ?>">&raquo;</a></li>
      <?php endif; ?>
    </ul>
  </nav>
  <div class="text-center text-muted small mb-3">
    <?= Text::sprintf('COM_BREEZINGFORMSNG_PAGINATION_INFO', $limitStart + 1, min($limitStart + $limit, $total), $total); ?>
  </div>
  <?php endif; ?>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="view" value="records">
  <input type="hidden" name="boxchecked" value="0">
  <input type="hidden" name="form_selection" value="<?= $this->formSelection; ?>">
  <input type="hidden" name="searchterm" value="<?= htmlspecialchars($this->searchTerm); ?>">
  <input type="hidden" name="filter_order" value="<?= htmlspecialchars($listOrder); ?>">
  <input type="hidden" name="filter_order_Dir" value="<?= htmlspecialchars($listDirn); ?>">
  <input type="hidden" name="limitstart" value="<?= $limitStart; ?>">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<?php
// Web assets for this view are registered in Records\HtmlView::display() —
// useScript() calls placed in the template body do not take effect here.
?>
