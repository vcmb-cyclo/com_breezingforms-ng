<?php

/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

/** @var \Vcmb\Component\BreezingformsNG\Administrator\View\Records\HtmlView $this */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$record = $this->record;
$tz = Factory::getApplication()->get('offset');
$submitted = htmlspecialchars((string) ($record->submitted ?? ''));
$formSelection = Factory::getApplication()->getInput()->getInt('form_selection', 0);
?>
<form action="index.php?option=com_breezingformsng" method="post" name="adminForm" id="adminForm">

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2">
        <span
          class="text-muted"
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="<?= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_RECORD_META'), ENT_QUOTES, 'UTF-8'); ?>"
          role="button"
          aria-label="<?= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_RECORD_META'), ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fas fa-info-circle"></i>
        </span>
      </div>
      <div class="row">
        <div class="col-sm-3">
          <dl class="row mb-0">
            <dt class="col-sm-4"><?= Text::_('COM_BREEZINGFORMSNG_ID'); ?></dt>
            <dd class="col-sm-8"><?= (int) $record->id; ?></dd>
            <dt class="col-sm-4"><?= Text::_('COM_BREEZINGFORMSNG_SUBMITTED'); ?></dt>
            <dd class="col-sm-8"><?= $submitted; ?></dd>
          </dl>
        </div>
        <div class="col-sm-9">
          <dl class="row mb-0">
            <dt class="col-sm-4"><?= Text::_('COM_BREEZINGFORMSNG_FORM'); ?></dt>
            <dd class="col-sm-8"><?= htmlspecialchars((string) ($record->form_title ?? '')); ?></dd>
            <dt class="col-sm-4"><?= Text::_('COM_BREEZINGFORMSNG_USER'); ?></dt>
            <dd class="col-sm-8">
              <?= htmlspecialchars(
                (string) ($record->user_full_name ?: $record->username)
                  . ' (' . (string) ($record->ip ?? '') . ')',
                ENT_QUOTES,
                'UTF-8'
              ); ?>
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2">
        <span
          class="text-muted"
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="<?= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_RECORD_VALUES'), ENT_QUOTES, 'UTF-8'); ?>"
          role="button"
          aria-label="<?= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_RECORD_VALUES'), ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fas fa-info-circle"></i>
        </span>
      </div>
      <?php foreach ($this->recordRows as $row): ?>
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label" for="element_<?= (int) $row['element_id']; ?>">
            <strong><?= htmlspecialchars($row['title']); ?></strong>
            <small class="text-muted">(<?= htmlspecialchars($row['name']); ?>)</small>
          </label>
          <div class="col-sm-9">
            <textarea
              id="element_<?= (int) $row['element_id']; ?>"
              name="element[<?= (int) $row['element_id']; ?>]"
              class="form-control"
              rows="<?= (substr_count($row['value'], "\n") > 0) ? min(10, substr_count($row['value'], "\n") + 2) : 1; ?>"><?= htmlspecialchars($row['value']); ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <input type="hidden" name="task" value="records.save">
  <input type="hidden" name="record_id" value="<?= (int) $record->id; ?>">
  <input type="hidden" name="form_selection" value="<?= $formSelection; ?>">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<nav class="d-flex justify-content-between mt-3" aria-label="<?= Text::_('JLIB_HTML_PAGINATION'); ?>">
  <?php if ($this->prevRecordId !== null): ?>
    <a class="btn btn-secondary"
      href="index.php?option=com_breezingformsng&amp;view=records&amp;layout=edit&amp;record_id=<?= $this->prevRecordId; ?>&amp;form_selection=<?= $formSelection; ?>">
      &laquo; <?= Text::_('JPREVIOUS'); ?>
    </a>
  <?php else: ?>
    <span class="btn btn-secondary disabled">&laquo; <?= Text::_('JPREVIOUS'); ?></span>
  <?php endif; ?>

  <?php if ($this->nextRecordId !== null): ?>
    <a class="btn btn-secondary"
      href="index.php?option=com_breezingformsng&amp;view=records&amp;layout=edit&amp;record_id=<?= $this->nextRecordId; ?>&amp;form_selection=<?= $formSelection; ?>">
      <?= Text::_('JNEXT'); ?> &raquo;
    </a>
  <?php else: ?>
    <span class="btn btn-secondary disabled"><?= Text::_('JNEXT'); ?> &raquo;</span>
  <?php endif; ?>
</nav>

<?php
// Web assets for this view are registered in Records\HtmlView::prepareEditToolbar() —
// useScript() calls placed in the template body do not take effect here.
?>