<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$f   = $this->form;
$pkg = $this->pkg ?: (string) ($f->package ?? '');

$tabId = 'bfFormTabs';

HTMLHelper::_('bootstrap.tab');

function bfSel(array $list, string $name, int $current, string $extra = ''): string
{
    $out = '<select class="form-select" name="' . htmlspecialchars($name) . '" ' . $extra . '>';
    $out .= '<option value="0">' . htmlspecialchars(\Joomla\CMS\LanguageText::_('COM_BREEZINGFORMSNG_FORMS_NONE')) . '</option>';
    foreach ($list as $item) {
        $sel  = (int) $item->id === (int) $current ? ' selected' : '';
        $out .= '<option value="' . (int) $item->id . '"' . $sel . '>' . htmlspecialchars($item->text) . '</option>';
    }
    $out .= '</select>';
    return $out;
}

$editor = Editor::getInstance('codemirror');
?>
<form action="index.php?option=com_breezingformsng" method="post" name="adminForm" id="adminForm">

  <ul class="nav nav-tabs" id="<?= $tabId; ?>" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#pane-general"
              type="button" role="tab"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_TAB_GENERAL'); ?></button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-email" data-bs-toggle="tab" data-bs-target="#pane-email"
              type="button" role="tab"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_TAB_EMAIL'); ?></button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-scripts" data-bs-toggle="tab" data-bs-target="#pane-scripts"
              type="button" role="tab"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_TAB_SCRIPTS'); ?></button>
    </li>
  </ul>

  <div class="tab-content border border-top-0 p-3 mb-3">

    <!-- TAB: GÉNÉRAL -->
    <div class="tab-pane fade show active" id="pane-general" role="tabpanel">

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_title"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_TITLE'); ?> <span class="text-danger">*</span></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_title" name="title" required value="<?= htmlspecialchars($f->title ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_name"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_NAME'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_name" name="name" value="<?= htmlspecialchars($f->name ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_package"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_PACKAGE'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_package" name="package" value="<?= htmlspecialchars($pkg); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_description"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_DESCRIPTION'); ?></label>
        <div class="col-sm-9"><textarea class="form-control" id="jf_description" name="description" rows="3"><?= htmlspecialchars($f->description ?? ''); ?></textarea></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_pages"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_PAGES'); ?></label>
        <div class="col-sm-9"><input type="number" class="form-control w-auto" id="jf_pages" name="pages" min="1" value="<?= (int) ($f->pages ?? 1); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_class1"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_CLASS'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_class1" name="class1" value="<?= htmlspecialchars($f->class1 ?? 'content_outline'); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_WIDTH'); ?></label>
        <div class="col-sm-9 d-flex gap-2 align-items-center">
          <input type="number" class="form-control w-auto" name="width" min="0" value="<?= (int) ($f->width ?? 400); ?>">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="widthmode" id="wm0" value="0"<?= !(int) ($f->widthmode ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="wm0">px</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="widthmode" id="wm1" value="1"<?= (int) ($f->widthmode ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="wm1">%</label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_HEIGHT'); ?></label>
        <div class="col-sm-9 d-flex gap-2 align-items-center">
          <input type="number" class="form-control w-auto" name="height" min="0" value="<?= (int) ($f->height ?? 500); ?>">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="heightmode" id="hm0" value="0"<?= !(int) ($f->heightmode ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="hm0"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_FIXED'); ?></label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="heightmode" id="hm1" value="1"<?= (int) ($f->heightmode ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="hm1"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_AUTO'); ?></label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('JPUBLISHED'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="published" id="pub1" value="1"<?= (int) ($f->published ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="pub1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="published" id="pub0" value="0"<?= !(int) ($f->published ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="pub0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_ordering"><?= Text::_('JORDER'); ?></label>
        <div class="col-sm-9"><input type="number" class="form-control w-auto" id="jf_ordering" name="ordering" value="<?= (int) ($f->ordering ?? 0); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_RUNMODE'); ?></label>
        <div class="col-sm-9">
          <select class="form-select" name="runmode">
            <option value="0"<?= (int) ($f->runmode ?? 0) === 0 ? ' selected' : ''; ?>><?= Text::_('COM_BREEZINGFORMSNG_FORMS_RUNMODE_FRONTEND'); ?></option>
            <option value="1"<?= (int) ($f->runmode ?? 0) === 1 ? ' selected' : ''; ?>><?= Text::_('COM_BREEZINGFORMSNG_FORMS_RUNMODE_BACKEND'); ?></option>
            <option value="2"<?= (int) ($f->runmode ?? 0) === 2 ? ' selected' : ''; ?>><?= Text::_('COM_BREEZINGFORMSNG_FORMS_RUNMODE_BOTH'); ?></option>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_DBLOG'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="dblog" id="dbl1" value="1"<?= (int) ($f->dblog ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="dbl1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="dblog" id="dbl0" value="0"<?= !(int) ($f->dblog ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="dbl0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

    </div><!-- /tab general -->

    <!-- TAB: NOTIFICATIONS EMAIL -->
    <div class="tab-pane fade" id="pane-email" role="tabpanel">

      <h5 class="mt-2"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_EMAIL_ADMIN'); ?></h5>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_EMAILNTF'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <?php foreach ([0 => 'JNONE', 1 => 'JYES', 2 => 'COM_BREEZINGFORMSNG_FORMS_EMAILLOG_ONLY'] as $v => $key): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="emailntf" id="entf<?= $v; ?>" value="<?= $v; ?>"<?= (int) ($f->emailntf ?? 1) === $v ? ' checked' : ''; ?>>
              <label class="form-check-label" for="entf<?= $v; ?>"><?= Text::_($key); ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_emailadr"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_EMAILADR'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_emailadr" name="emailadr" value="<?= htmlspecialchars($f->emailadr ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_custom_mail_subject"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MAILSUBJECT'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_custom_mail_subject" name="custom_mail_subject" value="<?= htmlspecialchars($f->custom_mail_subject ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_alt_mailfrom"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MAILFROM'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_alt_mailfrom" name="alt_mailfrom" value="<?= htmlspecialchars($f->alt_mailfrom ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_alt_fromname"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_FROMNAME'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_alt_fromname" name="alt_fromname" value="<?= htmlspecialchars($f->alt_fromname ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_EMAILLOG'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="emaillog" id="elog1" value="1"<?= (int) ($f->emaillog ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="elog1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="emaillog" id="elog0" value="0"<?= !(int) ($f->emaillog ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="elog0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_EMAILXML'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="emailxml" id="exml1" value="1"<?= (int) ($f->emailxml ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="exml1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="emailxml" id="exml0" value="0"<?= !(int) ($f->emailxml ?? 0) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="exml0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

      <hr>
      <h5><?= Text::_('COM_BREEZINGFORMSNG_FORMS_EMAIL_USER'); ?></h5>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MB_EMAILNTF'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <?php foreach ([0 => 'JNONE', 1 => 'JYES', 2 => 'COM_BREEZINGFORMSNG_FORMS_EMAILLOG_ONLY'] as $v => $key): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="mb_emailntf" id="mbentf<?= $v; ?>" value="<?= $v; ?>"<?= (int) ($f->mb_emailntf ?? 1) === $v ? ' checked' : ''; ?>>
              <label class="form-check-label" for="mbentf<?= $v; ?>"><?= Text::_($key); ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_mb_emailadr"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MB_EMAILADR'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_mb_emailadr" name="mb_emailadr" value="<?= htmlspecialchars($f->mb_emailadr ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_mb_custom_mail_subject"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MB_MAILSUBJECT'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_mb_custom_mail_subject" name="mb_custom_mail_subject" value="<?= htmlspecialchars($f->mb_custom_mail_subject ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_mb_alt_mailfrom"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MB_MAILFROM'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_mb_alt_mailfrom" name="mb_alt_mailfrom" value="<?= htmlspecialchars($f->mb_alt_mailfrom ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label" for="jf_mb_alt_fromname"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MB_FROMNAME'); ?></label>
        <div class="col-sm-9"><input type="text" class="form-control" id="jf_mb_alt_fromname" name="mb_alt_fromname" value="<?= htmlspecialchars($f->mb_alt_fromname ?? ''); ?>"></div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_MB_EMAILLOG'); ?></label>
        <div class="col-sm-9 d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mb_emaillog" id="mbelog1" value="1"<?= (int) ($f->mb_emaillog ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="mbelog1"><?= Text::_('JYES'); ?></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mb_emaillog" id="mbelog0" value="0"<?= !(int) ($f->mb_emaillog ?? 1) ? ' checked' : ''; ?>>
            <label class="form-check-label" for="mbelog0"><?= Text::_('JNO'); ?></label>
          </div>
        </div>
      </div>

    </div><!-- /tab email -->

    <!-- TAB: SCRIPTS & PIÈCES -->
    <div class="tab-pane fade" id="pane-scripts" role="tabpanel">

      <!-- Init script -->
      <div class="card mb-3">
        <div class="card-header"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_SCRIPT_INIT'); ?></div>
        <div class="card-body">
          <div class="row mb-2">
            <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_SCRIPT_SOURCE'); ?></label>
            <div class="col-sm-9 d-flex gap-3">
              <?php foreach ([0 => 'JNONE', 1 => 'COM_BREEZINGFORMSNG_FORMS_LIBRARY', 2 => 'COM_BREEZINGFORMSNG_FORMS_INLINE'] as $v => $k): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="script1cond" id="s1c<?= $v; ?>" value="<?= $v; ?>"
                         onchange="bfToggle('s1lib','s1code',this.value)"<?= (int) ($f->script1cond ?? 0) === $v ? ' checked' : ''; ?>>
                  <label class="form-check-label" for="s1c<?= $v; ?>"><?= Text::_($k); ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div id="s1lib" style="display:<?= (int) ($f->script1cond ?? 0) === 1 ? '' : 'none'; ?>">
            <?= bfSel($this->initScripts, 'script1id', (int) ($f->script1id ?? 0)); ?>
          </div>
          <div id="s1code" style="display:<?= (int) ($f->script1cond ?? 0) === 2 ? '' : 'none'; ?>">
            <?= $editor->display('script1code', htmlspecialchars($f->script1code ?? ''), '100%', '200px', 60, 10, false, 'jf_script1code', null, null, ['syntax' => 'javascript']); ?>
          </div>
        </div>
      </div>

      <!-- Submitted script -->
      <div class="card mb-3">
        <div class="card-header"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_SCRIPT_SUBMITTED'); ?></div>
        <div class="card-body">
          <div class="row mb-2">
            <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_SCRIPT_SOURCE'); ?></label>
            <div class="col-sm-9 d-flex gap-3">
              <?php foreach ([0 => 'JNONE', 1 => 'COM_BREEZINGFORMSNG_FORMS_LIBRARY', 2 => 'COM_BREEZINGFORMSNG_FORMS_INLINE'] as $v => $k): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="script2cond" id="s2c<?= $v; ?>" value="<?= $v; ?>"
                         onchange="bfToggle('s2lib','s2code',this.value)"<?= (int) ($f->script2cond ?? 0) === $v ? ' checked' : ''; ?>>
                  <label class="form-check-label" for="s2c<?= $v; ?>"><?= Text::_($k); ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div id="s2lib" style="display:<?= (int) ($f->script2cond ?? 0) === 1 ? '' : 'none'; ?>">
            <?= bfSel($this->submittedScripts, 'script2id', (int) ($f->script2id ?? 0)); ?>
          </div>
          <div id="s2code" style="display:<?= (int) ($f->script2cond ?? 0) === 2 ? '' : 'none'; ?>">
            <?= $editor->display('script2code', htmlspecialchars($f->script2code ?? ''), '100%', '200px', 60, 10, false, 'jf_script2code', null, null, ['syntax' => 'javascript']); ?>
          </div>
        </div>
      </div>

      <!-- Additional script code -->
      <div class="card mb-3">
        <div class="card-header"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_SCRIPT3'); ?></div>
        <div class="card-body">
          <?= $editor->display('script3code', htmlspecialchars($f->script3code ?? ''), '100%', '200px', 60, 10, false, 'jf_script3code', null, null, ['syntax' => 'javascript']); ?>
        </div>
      </div>

      <?php
      $pieceLabels = [
          1 => ['COM_BREEZINGFORMSNG_FORMS_PIECE_BEFORE',       $this->pieceBefore,      'piece1cond', 'piece1id', 'piece1code', 'p1'],
          2 => ['COM_BREEZINGFORMSNG_FORMS_PIECE_AFTER',        $this->pieceAfter,       'piece2cond', 'piece2id', 'piece2code', 'p2'],
          3 => ['COM_BREEZINGFORMSNG_FORMS_PIECE_BEGIN_SUBMIT', $this->pieceBeginSubmit, 'piece3cond', 'piece3id', 'piece3code', 'p3'],
          4 => ['COM_BREEZINGFORMSNG_FORMS_PIECE_END_SUBMIT',   $this->pieceEndSubmit,   'piece4cond', 'piece4id', 'piece4code', 'p4'],
      ];
      foreach ($pieceLabels as [$label, $pieces, $condName, $idName, $codeName, $pfx]): ?>
      <div class="card mb-3">
        <div class="card-header"><?= Text::_($label); ?></div>
        <div class="card-body">
          <div class="row mb-2">
            <label class="col-sm-3 col-form-label"><?= Text::_('COM_BREEZINGFORMSNG_FORMS_SCRIPT_SOURCE'); ?></label>
            <div class="col-sm-9 d-flex gap-3">
              <?php foreach ([0 => 'JNONE', 1 => 'COM_BREEZINGFORMSNG_FORMS_LIBRARY', 2 => 'COM_BREEZINGFORMSNG_FORMS_INLINE'] as $v => $k): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="<?= $condName; ?>" id="<?= $pfx; ?>c<?= $v; ?>"
                         value="<?= $v; ?>" onchange="bfToggle('<?= $pfx; ?>lib','<?= $pfx; ?>code',this.value)"
                         <?= (int) ($f->$condName ?? 0) === $v ? ' checked' : ''; ?>>
                  <label class="form-check-label" for="<?= $pfx; ?>c<?= $v; ?>"><?= Text::_($k); ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div id="<?= $pfx; ?>lib" style="display:<?= (int) ($f->$condName ?? 0) === 1 ? '' : 'none'; ?>">
            <?= bfSel($pieces, $idName, (int) ($f->$idName ?? 0)); ?>
          </div>
          <div id="<?= $pfx; ?>code" style="display:<?= (int) ($f->$condName ?? 0) === 2 ? '' : 'none'; ?>">
            <?= $editor->display($codeName, htmlspecialchars($f->$codeName ?? ''), '100%', '200px', 60, 10, false, 'jf_' . $codeName, null, null, ['syntax' => 'php']); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    </div><!-- /tab scripts -->

  </div><!-- /.tab-content -->

  <input type="hidden" name="id" value="<?= (int) ($f->id ?? 0); ?>">
  <input type="hidden" name="task" value="">
  <?= HTMLHelper::_('form.token'); ?>
</form>

<?php
$bfDocument = Factory::getApplication()->getDocument();
$bfDocument->getWebAssetManager()->useScript('com_breezingformsng.admin-form');
$bfDocument->addScriptOptions('com_breezingformsng.admin-form', ['cancelTask' => 'forms.cancel']);
?>
