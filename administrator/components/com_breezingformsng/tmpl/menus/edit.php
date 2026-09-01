<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

/** @var \Vcmb\Component\BreezingformsNG\Administrator\View\Menus\HtmlView $this */
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$item = $this->item;
$pkg  = $this->pkg;
?>
<form action="index.php?option=com_breezingformsng" method="post" name="adminForm" id="adminForm">

  <div class="card mb-3">
    <div class="card-header"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_EDIT'); ?></div>
    <div class="card-body">

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_title"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_TITLE'); ?></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="jf_title" name="title"
                 value="<?= htmlspecialchars($item->title ?? ''); ?>" required>
          <div class="form-text"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_TIPTITLE'); ?></div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_name"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_NAME'); ?></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="jf_name" name="name"
                 value="<?= htmlspecialchars($item->name ?? ''); ?>">
          <div class="form-text"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_TIPNAME'); ?></div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_package"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PACKAGE'); ?></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="jf_package" name="package"
                 value="<?= htmlspecialchars($item->package ?? $pkg); ?>">
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_parent"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PARENT'); ?></label>
        <div class="col-sm-9">
          <select class="form-select" id="jf_parent" name="parent">
            <option value="0"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_TOP'); ?></option>
            <?php foreach ($this->parents as $p): ?>
              <?php if ((int) $p->id !== (int) $item->id): ?>
                <option value="<?= (int) $p->id; ?>"<?= (int) ($item->parent ?? 0) === (int) $p->id ? ' selected' : ''; ?>>
                  <?= htmlspecialchars($p->title); ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_page"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PAGE'); ?></label>
        <div class="col-sm-9">
          <input type="number" class="form-control w-auto" id="jf_page" name="page" min="1"
                 value="<?= (int) ($item->page ?? 1); ?>">
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('JPUBLISHED'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="published" id="pub1" value="1"<?= (int) ($item->published ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="pub1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="published" id="pub0" value="0"<?= !(int) ($item->published ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="pub0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_FRAME'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="frame" id="fr1" value="1"<?= (int) ($item->frame ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="fr1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="frame" id="fr0" value="0"<?= !(int) ($item->frame ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="fr0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_BORDER'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="border" id="bo1" value="1"<?= (int) ($item->border ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="bo1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="border" id="bo0" value="0"<?= !(int) ($item->border ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="bo0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_params"><?= Text::_('COM_BREEZINGFORMSNG_MENUS_PARAMS'); ?></label>
        <div class="col-sm-9">
          <textarea class="form-control" id="jf_params" name="params" rows="4"><?= htmlspecialchars($item->params ?? ''); ?></textarea>
        </div>
      </div>

    </div>
  </div>

  <input type="hidden" name="id" value="<?= (int) ($item->id ?? 0); ?>">
  <input type="hidden" name="img" value="<?= htmlspecialchars($item->img ?? ''); ?>">
  <input type="hidden" name="task" value="">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<?php
/** @var \Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$bfDocument = $app->getDocument();
$bfDocument->getWebAssetManager()->useScript('com_breezingformsng.admin-form');
$bfDocument->addScriptOptions('com_breezingformsng.admin-form', ['cancelTask' => 'menus.cancel']);
?>
