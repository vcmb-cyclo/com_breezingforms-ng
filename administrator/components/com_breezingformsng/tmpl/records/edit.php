<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

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
    <div class="card-header">
      <strong><?= Text::_('COM_BREEZINGFORMSNG_RECORD_META'); ?></strong>
    </div>
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3"><?= Text::_('COM_BREEZINGFORMSNG_ID'); ?></dt>
        <dd class="col-sm-9"><?= (int) $record->id; ?></dd>
        <dt class="col-sm-3"><?= Text::_('COM_BREEZINGFORMSNG_SUBMITTED'); ?></dt>
        <dd class="col-sm-9"><?= $submitted; ?></dd>
        <dt class="col-sm-3"><?= Text::_('COM_BREEZINGFORMSNG_FORM'); ?></dt>
        <dd class="col-sm-9"><?= htmlspecialchars((string) ($record->form_title ?? '')); ?></dd>
        <dt class="col-sm-3"><?= Text::_('COM_BREEZINGFORMSNG_USER'); ?></dt>
        <dd class="col-sm-9"><?= htmlspecialchars((string) ($record->user_full_name ?: $record->username)); ?></dd>
        <dt class="col-sm-3"><?= Text::_('COM_BREEZINGFORMSNG_IP'); ?></dt>
        <dd class="col-sm-9"><?= htmlspecialchars((string) ($record->ip ?? '')); ?></dd>
      </dl>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <strong><?= Text::_('COM_BREEZINGFORMSNG_RECORD_VALUES'); ?></strong>
    </div>
    <div class="card-body">
      <?php foreach ($this->recordRows as $row): ?>
        <div class="mb-3">
          <label class="form-label" for="element_<?= (int) $row['element_id']; ?>">
            <strong><?= htmlspecialchars($row['title']); ?></strong>
            <small class="text-muted">(<?= htmlspecialchars($row['name']); ?>)</small>
          </label>
          <textarea
            id="element_<?= (int) $row['element_id']; ?>"
            name="element[<?= (int) $row['element_id']; ?>]"
            class="form-control"
            rows="<?= (substr_count($row['value'], "\n") > 0) ? min(10, substr_count($row['value'], "\n") + 2) : 2; ?>"
          ><?= htmlspecialchars($row['value']); ?></textarea>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <input type="hidden" name="task" value="records.save">
  <input type="hidden" name="record_id" value="<?= (int) $record->id; ?>">
  <input type="hidden" name="form_selection" value="<?= $formSelection; ?>">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<?php
$document = Factory::getApplication()->getDocument();
Text::script('COM_BREEZINGFORMSNG_TEST_NO_CHANGES');
Text::script('COM_BREEZINGFORMSNG_CONFIRM_DISCARD_CHANGES');
$document->addScriptOptions('com_breezingformsng.admin-form', [
    'cancelTask' => 'records.display',
    'saveTask' => 'records.save',
]);
$document->getWebAssetManager()->useScript('com_breezingformsng.admin-form-dirty');
?>
