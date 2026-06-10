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

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

if (BFRequest::getVar('act', '') == 'configuration') {
	ToolBarHelper::preferences('com_breezingformsng');
	ToolbarHelper::help(
		'COM_BREEZINGFORMSNG_HELP_CONFIGURATION_TITLE',
		false,
		Uri::base() . 'index.php?option=com_breezingformsng&view=help&section=configuration&tmpl=component'
	);
}

echo '<style type="text/css">
label{
    display: inline;
}
</style>';

class HTML_facileFormsConf
{
	static function edit($option, $caller, $pkg, $downloadfile = '')
	{
		global $ff_mossite, $ff_config, $ff_admsite, $ff_admicon, $ff_version;

		ToolBarHelper::custom('save', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_SAVE'), false);
		ToolBarHelper::custom('instpackage', 'upload', 'upload', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_PKGINSTLR'), false);
		ToolBarHelper::custom('makepackage', 'new.png', 'new_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_CREAPKG'), false);
		//ToolBarHelper::custom( 'cancel', 'cancel.png', 'cancel_f2.png', BFText::_( 'COM_BREEZINGFORMSNG_TOOLBAR_QUICKMODE_CLOSE' ), false );

		?>
		<form action="index.php" method="post" name="adminForm" id="adminForm">
			<table cellpadding="4" cellspacing="1" border="0" class="adminform">
				<!--<tr><th colspan="6" class="title" ><h2>BreezingForms <?php echo $ff_version . ' - ' . BFText::_('COM_BREEZINGFORMSNG_CONFIG'); ?></h2></th></tr>-->

				<?php
				$update_key = '';
				try {

					$db = Factory::getContainer()->get(DatabaseInterface::class);
					$db->setQuery("Select extra_query From #__update_sites Where `name` = 'BreezingForms NG'");
					$update_key = $db->loadResult();
					$update_key = explode('=', $update_key ?? '');
					if (isset($update_key[1])) {
						$update_key = $update_key[1];
					} else {
						$update_key = '';
					}

				} catch (Exception $e) {

				} catch (Error $e) {

				}
				?>
				<tr>
					<td></td>
					<td nowrap><label class="hasTip" title="Update key available at Crosstec.org">Update Key</label></td>
					<td nowrap colspan="3"><input type="text" name="updatekey"
							value="<?php echo htmlentities($update_key, ENT_QUOTES, 'UTF-8'); ?>" /></td>
					<td></td>
				</tr>

				<tr style="display: none;">
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_ENABLE_CLASSIC_TIP')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_ENABLE_CLASSIC'); ?>
						</label></td>
					<td nowrap colspan="3">
						<?php
						echo HTMLHelper::_('select.booleanlist', "enable_classic", "", $ff_config->enable_classic);
						?>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_DISABLE_IP_ADDRESS_STORING')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_DISABLE_IP_ADDRESS_STORING'); ?>
						</label></td>
					<td nowrap colspan="3">
						<?php
						echo HTMLHelper::_('select.booleanlist', "disable_ip", "", $ff_config->disable_ip);
						?>
					<td></td>
				</tr>
				<?php
				if ($ff_config->enable_classic == 1) {
					?>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_USELIVESITE'); ?>
						</td>
						<td nowrap colspan="3">
							<?php
							echo HTMLHelper::_('select.booleanlist', "livesite", "", $ff_config->livesite);
							?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_PREVIEWFRAME'); ?>
						</td>
						<td nowrap colspan="3">
							<?php
							echo HTMLHelper::_('select.booleanlist', "stylesheet", "", $ff_config->stylesheet);
							?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td valign="top" nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_GRIDSIZE'); ?>
						</td>
						<td nowrap colspan="3">
							<input type="text" size="4" maxlength="4" name="gridsize" value="<?php echo $ff_config->gridsize; ?>" />
						</td>
						<td></td>
					</tr>

					<tr>
						<td></td>
						<td valign="top" nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_COLOR'); ?> 1
						</td>
						<td nowrap colspan="3">
							<input type="text" size="7" maxlength="20" name="gridcolor1"
								value="<?php echo $ff_config->gridcolor1; ?>" />
						</td>
						<td></td>
					</tr>

					<tr>
						<td></td>
						<td valign="top" nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_COLOR'); ?> 2
						</td>
						<td nowrap colspan="3">
							<input type="text" size="7" maxlength="20" name="gridcolor2"
								value="<?php echo $ff_config->gridcolor2; ?>" />
						</td>
						<td></td>
					</tr>

					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_USEWYSIWYG'); ?>
						</td>
						<td nowrap colspan="3">
							<?php
							echo HTMLHelper::_('select.booleanlist', "wysiwyg", "", $ff_config->wysiwyg);
							?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_COMPRESS'); ?>
						</td>
						<td nowrap colspan="3">
							<?php
							echo HTMLHelper::_('select.booleanlist', "compress", "", $ff_config->compress);
							?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_GETPROVIDER'); ?>
						</td>
						<td nowrap colspan="3">
							<?php
							echo HTMLHelper::_('select.booleanlist', "getprovider", "", $ff_config->getprovider);
							?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_SMALL'); ?>
						</td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_MEDIUM'); ?>
						</td>
						<td nowrap width="100%">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_LARGE'); ?>
						</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_TEXTAREA'); ?>
						</td>
						<td nowrap><input type="text" style="width: 50px;" size="4" maxlength="4" name="areasmall"
								value="<?php echo $ff_config->areasmall; ?>" /></td>
						<td nowrap><input type="text" style="width: 50px;" size="4" maxlength="4" name="areamedium"
								value="<?php echo $ff_config->areamedium; ?>" /></td>
						<td nowrap><input type="text" style="width: 50px;" size="4" maxlength="4" name="arealarge"
								value="<?php echo $ff_config->arealarge; ?>" /></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_LIMITDESC'); ?>
						</td>
						<td nowrap colspan="3"><input type="text" size="6" maxlength="6" name="limitdesc"
								value="<?php echo $ff_config->limitdesc; ?>" />
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_CHARS'); ?>
						</td>
						<td></td>
					</tr>

					<?php
				}
				?>

				<tr>
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_DEFAULTEMAIL_TIP')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_DEFAULTEMAIL'); ?>
						</label></td>
					<td nowrap colspan="3"><input type="text" name="emailadr" value="<?php echo $ff_config->emailadr; ?>" />
					</td>
					<td></td>
				</tr>
				<?php
				if ($ff_config->enable_classic == 1) {
					?>
					<tr>
						<td></td>
						<td nowrap>
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_FFIMAGES'); ?>
						</td>
						<td nowrap colspan="3"><input type="text" size="70" maxlength="255" name="images"
								value="<?php echo $ff_config->images; ?>" /></td>
						<td></td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_FFUPLOADS_TIP')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_FFUPLOADS'); ?>
						</label></td>
					<td nowrap colspan="3"><input type="text" size="70" maxlength="255" name="uploads"
							value="<?php echo $ff_config->uploads; ?>" /></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVDELIMITER_TIP')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVDELIMITER'); ?>
						</label></td>
					<td nowrap colspan="3"><input type="text" size="1" maxlength="1" name="csvdelimiter"
							value="<?php echo htmlentities(stripslashes($ff_config->csvdelimiter), ENT_QUOTES, 'UTF-8'); ?>" />
					</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVQUOTE_TIP')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVQUOTE'); ?>
						</label></td>
					<td nowrap colspan="3"><input type="text" size="1" maxlength="1" name="csvquote"
							value="<?php echo htmlentities(stripslashes($ff_config->csvquote), ENT_QUOTES, 'UTF-8'); ?>" /></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td nowrap><label class="hasTip"
							title="<?php echo bf_tooltipText(BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVQUOTENEWLINE_TIP')); ?>">
							<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVQUOTENEWLINE'); ?>
						</label></td>
					<td nowrap colspan="3"><input type="radio" name="cellnewline" value="0" <?php echo $ff_config->cellnewline == 0 ? ' checked="checked"' : '' ?> />
						<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVQUOTENEWLINE_REGULAR'); ?> <input type="radio"
							name="cellnewline" value="1" <?php echo $ff_config->cellnewline != 0 ? ' checked="checked"' : '' ?> />
						<?php echo BFText::_('COM_BREEZINGFORMSNG_CONFIG_CSVQUOTENEWLINE_QUOTED'); ?>
					</td>
					<td></td>
				</tr>
			</table>
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="act" value="configuration" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="caller_url" value="<?php echo htmlspecialchars($caller, ENT_QUOTES); ?>" />
			<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
		</form>
		<?php
		if ($downloadfile != '') {
			?>
			<script type="text/javascript">onload = function () { document.dldform.submit(); }</script>
			<form action="<?php echo $ff_admsite; ?>/admin/download.php" method="post" name="dldform">
				<input type="hidden" name="filename" value="<?php echo $downloadfile; ?>" />
			</form>
			<?php
		} // if
	} // edit

	static function instPackage($option, $caller, $pkg, &$rows)
	{
		global $ff_mossite, $ff_config, $ff_admpath, $mainframe;
		$startdir = '{mospath}/administrator/components/com_breezingformsng/packages/samples.english.xml';
		?>
		<script type="text/javascript">
								<!--
								function submitbutton(pressbutton)
								{
									var form = document.adminForm;
									switch (pressbutton) {
										case 'delpkgs':
											if (form.boxchecked.value==0) {
												alert("<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELPKGSFIRST'); ?>");
			return;
											} // if
			break;
										default:
			break;
									} // switch
			switch (pressbutton) {
				case 'start':
					if (form.uploadpackage.checked) {
						if (form.uploadfile.value == '') {
							alert("<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NOUPLOADFILE'); ?>");
							form.uploadfile.focus();
							return;
						} // if
						pressbutton = 'uploadpackage';
					} else
						if (form.localpackage.checked) {
							if (form.installfile.value == '') {
								alert("<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NOFILENAME'); ?>");
								form.installfile.focus();
								return;
							} // if
							pressbutton = 'localpackage';
						} else {
							alert("<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELECTMODE'); ?>");
							return;
						} // if
					break;
				case 'delpkgs':
					if (!confirm("<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ASKUNINST'); ?>")) return;
					break;
				default:
					break;
			} // switch
			Joomla.submitform(pressbutton);
								} // submitbutton

			function clickInstMode(value) {
				var form = document.adminForm;
				switch (parseInt(value)) {
					case 0:
						document.getElementById('div_uploadpackage').style.display = '';
						document.getElementById('div_localpackage').style.display = 'none';
						break;
					default:
						document.getElementById('div_uploadpackage').style.display = 'none';
						document.getElementById('div_localpackage').style.display = '';
						break;
				} // switch
			} // clickInstMode
			//-->
		</script>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script type="text/javascript"
			src="<?php echo $ff_mossite; ?>/components/com_breezingformsng/libraries/js/overlib_mini.js"></script>

		<form action="index.php" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">

			<div style="margin-bottom: 20px;">
				<?php
				if ((bool) ini_get('file_uploads')) {
					$upldattr = ' checked="checked"';
					$uplddisp = '';
					$loclattr = '';
					$locldisp = ' style="display:none;"';
				} else {
					$upldattr = ' disabled="disabled"';
					$uplddisp = ' style="display:none;"';
					$loclattr = ' checked="checked"';
					$locldisp = '';
				} // if
				?>
				<input type="radio" id="uploadpackage" name="tasksel" value="0" onclick="clickInstMode(this.value)" <?php echo $upldattr; ?> />
				<label for="uploadpackage">
					<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CLIENTUPLOAD'); ?>
				</label>
				<br />
				<input type="radio" id="localpackage" name="tasksel" value="1" onclick="clickInstMode(this.value)" <?php echo $loclattr; ?> />
				<label for="localpackage">
					<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SERVERINSTALL'); ?>
				</label>
				<div id="div_uploadpackage" <?php echo $uplddisp; ?>>
					<br />
					<input type="file" id="uploadfile" name="uploadfile" size="70" />
				</div>
				<div id="div_localpackage" <?php echo $locldisp; ?>>
					<br />
					<input type="text" id="installfile" name="installfile" size="80" value="<?php echo $startdir; ?>" />
				</div>
			</div>

			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist table table-striped">
				<tr>
					<th nowrap align="center"><input type="checkbox" name="toggle" value="" onclick='Joomla.checkAll(this);' />
					</th>
					<th style="width: 70%" nowrap colspan="4" align="left">
						<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE'); ?>
					</th>
					<th nowrap colspan="2" align="left">
						<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR'); ?>
					</th>
				</tr>
				<?php
				$k = 0;
				for ($i = 0; $i < count($rows); $i++) {
					$row = $rows[$i];
					$url = htmlspecialchars($row->url);
					if (substr($url, 0, 7) == 'http://')
						$url = substr($url, 7);
					$email = htmlspecialchars($row->email);
					if (substr($email, 0, 7) == 'mailto:')
						$email = substr($email, 7);
					?>
					<tr class="row<?php echo $k; ?>">
						<td nowrap valign="top" align="center"><input type="checkbox" id="cb<?php echo $i; ?>" name="ids[]"
								value="<?php echo $row->id; ?>" onclick="Joomla.isChecked(this.checked);" />
						</td>
						<td nowrap valign="top" align="left">
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ID'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NAME'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_VERS'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CREATEDATE'); ?>
							</strong>
						</td>
						<td nowrap valign="top" align="left">
							<?php echo htmlspecialchars($row->id); ?><br />
							<?php echo htmlspecialchars($row->name); ?><br />
							<?php echo htmlspecialchars($row->version); ?><br />
							<?php echo htmlspecialchars($row->created); ?>
						</td>
						<td nowrap valign="top" align="left">
							<br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_TITLE'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_DESC'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CPYRT'); ?>
							</strong>
						</td>
						<td wrap valign="top" align="left" >
							<br />
							<?php echo htmlspecialchars($row->title); ?><br />
							<?php echo htmlspecialchars($row->description); ?><br />
							<?php echo htmlspecialchars($row->copyright); ?>
						</td>
						<td nowrap valign="top" align="left">
							<br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NAME'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_EMAIL'); ?>
							</strong><br />
							<strong>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_URL'); ?>
							</strong>
						</td>
						<td nowrap valign="top" align="left" width="100%">
							<br />
							<?php echo htmlspecialchars($row->author); ?><br />
							<?php if ($email != '')
								echo '<a href="mailto:' . $email . '">' . $email . '</a>';
							else
								echo '&nbsp;'; ?><br />
							<?php if ($url != '')
								echo '<a href="http://' . $url . '" target="_blank">http://' . $url . '</a>';
							else
								echo '&nbsp;'; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				} // for
				?>
			</table>
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="act" value="configuration" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="caller_url" value="<?php echo htmlspecialchars($caller, ENT_QUOTES); ?>" />
			<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
		</form>
		<?php
	} // instPackage

	static function makePackage($option, $caller, $pkg, $lists)
	{
		global $ff_mossite, $ff_config;
		$startdir = '{mossite}/administrator/components/com_breezingformsng/packages';
		$rows = $lists['pkgnames'];
		$empty = false;
		$selpkg = '';
		if (count($rows))
			foreach ($rows as $row) {
				if ($row->package == '')
					$empty = true;
				if ($row->package == $pkg)
					$selpkg = $pkg;
			} // foreach

		ToolBarHelper::custom('mkpackage', 'save.png', 'save_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_SAVE_PACKAGE'), false);
		ToolBarHelper::custom('edit', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_CLOSE'), false);

		?>

		<script type="text/javascript">
			<!--
			onload = function()
			{
				var form = document.adminForm;
		<?php
		echo "\t\t\t\tpkgChange('$selpkg');\n"
			?>
			form.pkg.focus();
									} // onload

			function setSelection(topic, selected) {
				var form = document.adminForm;
				var opts;
				switch (topic) {
					case 2: opts = form.id_menusel.options; break;
					case 3: opts = form.id_scriptsel.options; break;
					case 4: opts = form.id_piecesel.options; break;
					default: opts = form.id_formsel.options;
				} // switch
				for (var i = 0; i < opts.length; i++) opts[i].selected = selected;
			} // setSelection

			var pkgs = [
				<?php
				$first = true;
				$pkgs = $lists['packages'];
				if (count($pkgs))
					foreach ($pkgs as $pkg1) {
						if (!$first)
							echo ",\n";
						echo "\t\t\t\t[" .
							"\"" . addslashes($pkg1->id) . "\"," .
							"\"" . addslashes($pkg1->name) . "\"," .
							"\"" . addslashes($pkg1->version) . "\"," .
							"\"" . addslashes($pkg1->title) . "\"," .
							"\"" . addslashes($pkg1->author) . "\"," .
							"\"" . addslashes($pkg1->email) . "\"," .
							"\"" . addslashes($pkg1->url) . "\"," .
							"\"" . addslashes($pkg1->description) . "\"," .
							"\"" . addslashes($pkg1->copyright) . "\"]";
						$first = false;
					} // foreach
				echo "\n";
				?>
			];

			function pkgChange(pkg) {
				if (pkg == '') {
					document.getElementById('menusel').style.display = '';
					document.getElementById('scriptsel').style.display = '';
				} else {
					document.getElementById('menusel').style.display = 'none';
					document.getElementById('scriptsel').style.display = 'none';
				} // if
				var p;
				var form = document.adminForm;
				for (p = 0; p < pkgs.length; p++) {
					if (pkgs[p][0] == pkg) {
						form.pkg_name.value = pkgs[p][1];
						form.pkg_version.value = pkgs[p][2];
						form.pkg_title.value = pkgs[p][3];
						form.pkg_author.value = pkgs[p][4];
						form.pkg_email.value = pkgs[p][5];
						form.pkg_url.value = pkgs[p][6];
						form.pkg_description.value = pkgs[p][7];
						form.pkg_copyright.value = pkgs[p][8];
						break;
					} // if
				} // for
			} // pkgChange

			function submitbutton(pressbutton) {
				if (pressbutton == 'mkpackage') {
					var invalidChars = /\W\./;
					var form = document.adminForm;
					error = '';
					foc = '';
					if (form.pkg_name.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTPACKAGENAME'); ?>\n";
						if (foc == '') foc = form.pkg_name;
					} else
						if (invalidChars.test(form.pkg_name.value)) {
							error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTIDENTIFIER'); ?>\n";
							if (foc == '') foc = form.pkg_name;
						} // if
					if (form.pkg_version.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTVERSION'); ?>\n";
						if (foc == '') foc = form.pkg_version;
					} // if
					if (form.pkg_title.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTTITLE'); ?>\n";
						if (foc == '') foc = form.pkg_title;
					} // if
					if (form.pkg_author.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTAUTHORNM'); ?>\n";
						if (foc == '') foc = form.pkg_author;
					} // if
					if (form.pkg_email.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTAUTHORMAIL'); ?>\n";
						if (foc == '') foc = form.pkg_email;
					} // if
					if (form.pkg_url.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTAUTHORURL'); ?>\n";
						if (foc == '') foc = form.pkg_url;
					} // if
					if (form.pkg_description.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTDESCR'); ?>\n";
						if (foc == '') foc = form.pkg_description;
					} // if
					if (form.pkg_copyright.value == '') {
						error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ENTCOPYRT'); ?>\n";
						if (foc == '') foc = form.pkg_copyright;
					} // if
					if (form.pkg.value == '') {
						var cnt = 0;
						var i;
						opts = form.id_scriptsel.options;
						for (i = 0; i < opts.length; i++) if (opts[i].selected) cnt++;
						opts = form.id_piecesel.options;
						for (i = 0; i < opts.length; i++) if (opts[i].selected) cnt++;
						opts = form.id_formsel.options;
						for (i = 0; i < opts.length; i++) if (opts[i].selected) cnt++;
						opts = form.id_menusel.options;
						for (i = 0; i < opts.length; i++) if (opts[i].selected) cnt++;
						if (cnt == 0)
							error += "<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELECTAPKG'); ?>\n";
					} // if
					if (error) {
						alert(error);
						if (foc != '') foc.focus();
						return;
					} // if
				} // if
				Joomla.submitform(pressbutton);
			} // submitbutton
			//-->
			if (typeof jQuery != "undefined") {
				jQuery(document).ready(function () {
					jQuery('.chzn-container').remove();
					jQuery('.inputbox').css('display', 'block');
				});
			}
		</script>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script type="text/javascript"
			src="<?php echo $ff_mossite; ?>/components/com_breezingformsng/libraries/js/overlib_mini.js"></script>
		<form action="index.php" method="post" id="adminForm" name="adminForm">
			<table cellpadding="4" cellspacing="1" border="0" class="adminform" style="width:100%;">
				<tr>
					<th colspan="4" class="title">BreezingForms -
						<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CREATEPKG'); ?>
					</th>
				</tr>
				<tr>
					<td></td>
					<td colspan="2">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ID'); ?>:
								</td>
								<td nowrap width="100%">
									<select id="id_pkg" name="pkg" class="inputbox" onchange="pkgChange(this.value);" size="1">
										<?php
										$rows = $lists['pkgnames'];
										if (!$empty) {
											if ($pkg == '')
												$sel = ' selected="selected"';
											else
												$sel = '';
											echo '<option value=""' . $sel . '></option>';
										} // if
										if (count($rows))
											foreach ($rows as $row) {
												if ($pkg == $row->package)
													$sel = ' selected="selected"';
												else
													$sel = '';
												echo '<option value="' . $row->package . '"' . $sel . '>' . $row->package . '</option>';
											} // foreach
										?>
									</select>
								</td>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NAME'); ?>:
								</td>
								<td nowrap width="100%"><input type="text" name="pkg_name" size="30" maxlength="30" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_VERS'); ?>:
								</td>
								<td nowrap><input type="text" name="pkg_version" size="30" maxlength="30" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_TITLE'); ?>:
								</td>
								<td nowrap width="100%"><input type="text" name="pkg_title" size="50" maxlength="50" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NAME'); ?>:
								</td>
								<td nowrap><input type="text" name="pkg_author" size="50" maxlength="50" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_EMAIL'); ?>:
								</td>
								<td nowrap><input type="text" name="pkg_email" size="50" maxlength="50" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_URL'); ?>:
								</td>
								<td nowrap><input type="text" name="pkg_url" size="50" maxlength="50" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_DESC'); ?>:
								</td>
								<td nowrap><input type="text" name="pkg_description" size="100" maxlength="100" /></td>
							</tr>
							<tr>
								<td nowrap>
									<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CPYRT'); ?>:
								</td>
								<td nowrap><input type="text" name="pkg_copyright" size="100" maxlength="100" /></td>
							</tr>
						</table>
					</td>
					<td></td>
				</tr>
				<tr id="menusel" style="display:none;">
					<td></td>
					<td>
						<fieldset>
							<legend>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_FORMSEL'); ?>
							</legend>
							<table align="center" cellpadding="4" cellspacing="1" border="0" width="100%">
								<tr>
									<td nowrap style="text-align:center">
										<select id="id_formsel" name="formsel[]" class="inputbox" size="15" multiple="multiple"
											style="width: 100%;">
											<?php
											$rows = $lists['forms'];
											for ($i = 0; $i < count($rows); $i++) {
												$row = $rows[$i];
												echo '<option value="' . $row->id . '">' . $row->title . '</option>';
											} // for
											?>
										</select><br /><br />
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELECTALL'); ?>"
											onclick="setSelection(1,true)" />&nbsp;
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CLRSELECTION'); ?>"
											onclick="setSelection(1,false)" />
									</td>
								</tr>
							</table>
						</fieldset>
					</td>
					<td nowrap>
						<fieldset>
							<legend>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_MENUSEL'); ?>
							</legend>
							<table align="center" cellpadding="4" cellspacing="1" border="0" width="100%">
								<tr>
									<td nowrap style="text-align:center">
										<select id="id_menusel" name="menusel[]" class="inputbox" size="15" multiple="multiple"
											style="width: 100%;">
											<?php
											$rows = $lists['compmenus'];
											for ($i = 0; $i < count($rows); $i++) {
												$row = $rows[$i];
												echo '<option value="' . $row->id . '">' . $row->title . '</option>';
											} // for
											?>
										</select><br /><br />
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELECTALL'); ?>"
											onclick="setSelection(2,true)" />&nbsp;
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CLRSELECTION'); ?>"
											onclick="setSelection(2,false)" />
									</td>
								</tr>
							</table>
						</fieldset>
					</td>
					<td></td>
				</tr>
				<tr id="scriptsel" style="display:none;">
					<td></td>
					<td>
						<fieldset>
							<legend>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SCRIPTSEL'); ?>
							</legend>
							<table align="center" cellpadding="4" cellspacing="1" border="0" width="100%">
								<tr>
									<td nowrap style="text-align:center">
										<select id="id_scriptsel" name="scriptsel[]" class="inputbox" size="15"
											multiple="multiple" style="width: 100%;">
											<?php
											$rows = $lists['scripts'];
											for ($i = 0; $i < count($rows); $i++) {
												$row = $rows[$i];
												echo '<option value="' . $row->id . '">' . $row->title . '</option>';
											} // for
											?>
										</select><br /><br />
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELECTALL'); ?>"
											onclick="setSelection(3,true)" />&nbsp;
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CLRSELECTION'); ?>"
											onclick="setSelection(3,false)" />
									</td>
								</tr>
							</table>
						</fieldset>
					</td>
					<td>
						<fieldset>
							<legend>
								<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PIECESEL'); ?>
							</legend>
							<table align="center" cellpadding="4" cellspacing="1" border="0" width="100%">
								<tr>
									<td nowrap style="text-align:center">
										<select id="id_piecesel" name="piecesel[]" class="inputbox" size="15"
											multiple="multiple" style="width: 100%;">
											<?php
											$rows = $lists['pieces'];
											for ($i = 0; $i < count($rows); $i++) {
												$row = $rows[$i];
												echo '<option value="' . $row->id . '">' . $row->title . '</option>';
											} // for
											?>
										</select><br /><br />
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SELECTALL'); ?>"
											onclick="setSelection(4,true)" />&nbsp;
										<input class="button btn btn-primary" type="button"
											value="<?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CLRSELECTION'); ?>"
											onclick="setSelection(4,false)" />
									</td>
								</tr>
							</table>
						</fieldset>
					</td>
					<td></td>
				</tr>

			</table>
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="act" value="configuration" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="caller_url" value="<?php echo htmlspecialchars($caller, ENT_QUOTES); ?>" />
		</form>
		<?php
	} // makePackage

	static function message($option, $caller, $pkg, $message, $task = 'edit')
	{
		?>
		<script type="text/javascript">onload = function () { Joomla.submitform('<?php echo $task; ?>'); }</script>
		<form action="index.php" method="post" id="adminForm" name="adminForm">
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="act" value="configuration" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="caller_url" value="<?php echo htmlspecialchars($caller, ENT_QUOTES); ?>" />
			<input type="hidden" name="pkg" value="<?php echo $pkg; ?>" />
			<input type="hidden" name="mosmsg" value="<?php echo htmlentities($message, ENT_QUOTES, 'UTF-8'); ?>" />
		</form>
		<?php
	} // message

	static function showParam(&$inst, $tag)
	{
		if (array_key_exists($tag, $inst->params[0]))
			echo $inst->params[0][$tag];
	} // showParam

	static function showPackage($option, $caller, $pkgid, &$inst)
	{
		ToolBarHelper::custom('instpackage', 'cancel.png', 'cancel_f2.png', BFText::_('COM_BREEZINGFORMSNG_TOOLBAR_QUICKMODE_CLOSE'), false);

		$pkgid = array_key_exists('pkgid', $inst->params[0]) ? (string) $inst->params[0]['pkgid'] : '';
		$packageInfo = [
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ID') => $pkgid,
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_INSTTYPE') => (string) ($inst->params[0]['pkgtype'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_FFVERSION') => (string) ($inst->params[0]['pkgversion'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NAME') => (string) ($inst->params[0]['name'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_TITLE') => (string) ($inst->params[0]['title'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PACKAGE') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_VERS') => (string) ($inst->params[0]['version'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_DESC') => (string) ($inst->params[0]['description'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CPYRT') => (string) ($inst->params[0]['copyright'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_CREATEDATE') => (string) ($inst->params[0]['creationDate'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_NAME') => (string) ($inst->params[0]['author'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_EMAIL') => (string) ($inst->params[0]['authorEmail'] ?? ''),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_AUTHOR') . ' ' . BFText::_('COM_BREEZINGFORMSNG_INSTALLER_URL') => (string) ($inst->params[0]['authorUrl'] ?? ''),
		];
		$importResults = [
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_SCRIPTSIMP') => count($inst->scripts),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PIECESIMP') => count($inst->pieces),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_FORMSIMP') => count($inst->forms),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_ELEMSIMP') => count($inst->elements),
			BFText::_('COM_BREEZINGFORMSNG_INSTALLER_MENUSIMP') => count($inst->menus),
		];
		$warningCount = count($inst->warnings);
		$hasWarnings = $warningCount > 0;
		$statusAlertClass = $hasWarnings ? 'alert-warning' : 'alert-success';
		$statusBadgeClass = $hasWarnings ? 'bg-warning text-dark' : 'bg-success';
		$statusIconClass = $hasWarnings ? 'icon-warning text-warning' : 'icon-check text-success';
		$statusText = $hasWarnings ? BFText::_('COM_BREEZINGFORMSNG_INSTALLER_REPORT_STATUS_WARNING') : BFText::_('COM_BREEZINGFORMSNG_INSTALLER_REPORT_STATUS_SUCCESS');

		?>
		<form action="index.php" method="post" id="adminForm" name="adminForm">
			<div class="container-fluid">
				<div class="card">
					<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
						<h2 class="mb-0">BreezingForms - <?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_PKGREPORT'); ?></h2>
						<span class="badge <?php echo $statusBadgeClass; ?>"><?php echo htmlspecialchars($statusText, ENT_QUOTES); ?></span>
					</div>
					<div class="card-body">
						<div class="alert <?php echo $statusAlertClass; ?> d-flex align-items-start gap-3" role="status">
							<span class="<?php echo $statusIconClass; ?>" aria-hidden="true"></span>
							<div>
								<div class="fw-bold"><?php echo htmlspecialchars($statusText, ENT_QUOTES); ?></div>
								<div>
									<?php
									echo $hasWarnings
										? htmlspecialchars(BFText::_('COM_BREEZINGFORMSNG_INSTALLER_REPORT_SUMMARY_WARNING'), ENT_QUOTES)
										: htmlspecialchars(BFText::_('COM_BREEZINGFORMSNG_INSTALLER_REPORT_SUMMARY_SUCCESS'), ENT_QUOTES);
									?>
								</div>
							</div>
						</div>

						<div class="row g-3">
							<div class="col-12 col-lg-7">
								<div class="card h-100">
									<div class="card-header">
										<strong><?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_REPORT_PACKAGEINFO'); ?></strong>
									</div>
									<div class="card-body">
										<dl class="row mb-0">
											<?php foreach ($packageInfo as $label => $value) {
												if ($value === '') {
													continue;
												}
												?>
												<dt class="col-sm-5"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></dt>
												<dd class="col-sm-7"><?php echo nl2br(htmlspecialchars($value, ENT_QUOTES)); ?></dd>
											<?php } ?>
										</dl>
									</div>
								</div>
							</div>
							<div class="col-12 col-lg-5">
								<div class="card h-100">
									<div class="card-header">
										<strong><?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_REPORT_RESULTS'); ?></strong>
									</div>
									<div class="list-group list-group-flush">
										<?php foreach ($importResults as $label => $count) { ?>
											<div class="list-group-item d-flex justify-content-between align-items-center">
												<span class="d-inline-flex align-items-center gap-2">
													<span class="<?php echo $count > 0 ? 'icon-check text-success' : 'icon-minus text-muted'; ?>" aria-hidden="true"></span>
													<span><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span>
												</span>
												<span class="badge bg-secondary rounded-pill"><?php echo (int) $count; ?></span>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>

						<?php if ($hasWarnings) { ?>
							<div class="card border-warning-subtle mt-3">
								<div class="card-header bg-warning-subtle text-warning-emphasis">
									<span class="icon-warning text-warning" aria-hidden="true"></span>
									<strong><?php echo BFText::_('COM_BREEZINGFORMSNG_INSTALLER_WARNINGS'); ?></strong>
								</div>
								<ul class="list-group list-group-flush">
									<?php foreach ($inst->warnings as $warn) { ?>
										<li class="list-group-item d-flex align-items-start gap-2">
											<span class="icon-warning text-warning mt-1" aria-hidden="true"></span>
											<span><?php echo htmlspecialchars($warn, ENT_QUOTES); ?></span>
										</li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="act" value="configuration" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="caller_url" value="<?php echo htmlspecialchars($caller, ENT_QUOTES); ?>" />
			<input type="hidden" name="pkg" value="<?php echo $pkgid; ?>" />
		</form>
		<?php
	} // showPackage
} // class HTML_facileFormsConf
?>
