<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * @version     5.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL - EVH
 * @license     Released under the terms of the GNU General Public License
 **/

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering\QuickMode;

\defined('_JEXEC') or die;

use HTML_facileFormsProcessor;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;


class MobileRenderer
{

	public $forceMobileUrl = '';
	/**
	 * @var HTML_facileFormsProcessor
	 */
	private $p = null;
	private $dataObject = array();
	private $rootMdata = array();
	private $useErrorAlerts = false;
	private $useDefaultErrors = false;
	private $useBalloonErrors = false;
	private $rollover = false;
	private $rolloverColor = '';
	private $toggleFields = '';
	private $hasFlashUpload = false;
	private $flashUploadTicket = '';
	private $cancelImagePath = '';
	private $uploadImagePath = '';
	private $language_tag = '';

	private $hasResponsiveDatePicker = false;

	function __construct(HTML_facileFormsProcessor $p)
	{
		$this->p = $p;

		$default = ComponentHelper::getParams('com_languages')->get('site');
		$this->language_tag = $this->p->app->getLanguage()->getTag() != $default ? $this->p->app->getLanguage()->getTag() : 'zz-ZZ';

		$head = $this->p->app->getDocument()->getHeadData();
		$head['styleSheets'] = array();
		$head['style'] = array();
		$head['scripts'] = array();
		$head['script'] = array();
		$head['custom'] = array();
		$this->p->app->getDocument()->setHeadData($head);

		$this->dataObject = json_decode(bf_b64dec($this->p->formrow->template_code), true);

		$this->rootMdata = $this->dataObject['properties'];

		if ($this->p->app->getInput()->getString('ff_applic', '') != 'mod_facileforms' && $this->p->app->getInput()->getString('ff_applic', '') != 'plg_facileforms') {
			/* translatables */
			if (isset($this->rootMdata['title_translation' . $this->language_tag]) && $this->rootMdata['title_translation' . $this->language_tag] != '') {
				$this->rootMdata['title'] = $this->rootMdata['title_translation' . $this->language_tag];
				$this->p->app->getDocument()->setTitle($this->rootMdata['title']);
			}
			/* translatables end */
		}

		$this->useErrorAlerts = $this->rootMdata['useErrorAlerts'];
		$this->useDefaultErrors = isset($this->rootMdata['useDefaultErrors']) ? $this->rootMdata['useDefaultErrors'] : false;
		$this->useBalloonErrors = isset($this->rootMdata['useBalloonErrors']) ? $this->rootMdata['useBalloonErrors'] : false;
		$this->rollover = $this->rootMdata['rollover'];
		$this->rolloverColor = $this->rootMdata['rolloverColor'];
		$this->toggleFields = $this->parseToggleFields(isset($this->rootMdata['toggleFields']) ? $this->rootMdata['toggleFields'] : '[]');
		// loading theme
		$this->cancelImagePath = Uri::root(true) . '/media/breezingforms/themes/cancel.png';
		$this->uploadImagePath = Uri::root(true) . '/media/breezingforms/themes/upload.png';

		mt_srand();
		$this->flashUploadTicket = md5(strtotime('now') . mt_rand(0, mt_getrandmax()));

	}

	public function parseToggleFields($code)
	{
		/*
							   example codes:

							  turn on element bla if blub is on
							  turn off section bla if blub is on
							  turn on section bla if blub is off
							  turn off element bla if blub is off

										  if element opener is off set opener huhuu

							  syntax:
							  ACTION STATE TARGETCATEGORY TARGETNAME if SRCNAME is VALUE
						   */

		$parsed = '';
		$code = str_replace("\r", '', $code);
		$lines = explode("\n", $code);
		$linesCnt = count($lines);

		for ($i = 0; $i < $linesCnt; $i++) {
			$tokens = explode(' ', trim($lines[$i]));
			$tokensCnt = count($tokens);
			if ($tokensCnt >= 8) {
				$state = '';
				// rebuilding the state as it could be a value containing blanks
				for ($j = 7; $j < $tokensCnt; $j++) {
					if ($j + 1 < $tokensCnt)
						$state .= $tokens[$j] . ' ';
					else
						$state .= $tokens[$j];
				}
				$parsed .= '{ action: "' . $tokens[0] . '", state: "' . $tokens[1] . '", tCat: "' . $tokens[2] . '", tName: "' . $tokens[3] . '", statement: "' . $tokens[4] . '", sName: "' . $tokens[5] . '", condition: "' . $tokens[6] . '", value: "' . addslashes($state) . '" },';
			}
		}

		return "[" . rtrim($parsed, ",") . "]";
	}

	private function bfCalendarIsTruthy($mdata, $key)
	{
		return isset($mdata[$key]) && $mdata[$key] !== '' && $mdata[$key] !== '0' && $mdata[$key] !== 0 && $mdata[$key] !== false;
	}

	private function bfCalendarShowTimeEnabled($mdata)
	{
		return $this->bfCalendarIsTruthy($mdata, 'showTime');
	}

	private function bfCalendarToPickadateFormat($format)
	{
		$format = trim((string) $format);

		if ($format === '') {
			return 'yyyy-mm-dd';
		}

		$format = str_replace(
			array('%Y', '%y', '%m', '%d', '%e', '%B', '%b'),
			array('yyyy', 'yy', 'mm', 'dd', 'd', 'mmmm', 'mmm'),
			$format
		);
		$format = preg_replace('/\s*(%H|%I|%k|%l|%M|%S|%p).*/', '', $format);
		$format = trim($format);

		return $format !== '' ? $format : 'yyyy-mm-dd';
	}

	private function bfCalendarToPickadateFirstDay($firstDay)
	{
		$firstDay = (int) $firstDay;

		if ($firstDay < 1 || $firstDay > 7) {
			$firstDay = 1;
		}

		return $firstDay === 7 ? 0 : $firstDay;
	}

	private function bfCalendarSelectYears($mdata)
	{
		$minYear = (isset($mdata['minYear']) && is_numeric($mdata['minYear'])) ? max(0, (int) $mdata['minYear']) : 0;
		$maxYear = (isset($mdata['maxYear']) && is_numeric($mdata['maxYear'])) ? max(0, (int) $mdata['maxYear']) : 0;
		$range = $minYear + $maxYear;

		return $range > 0 ? max(10, $range + 1) : 60;
	}

	public function fetchHead($head)
	{
		$app = $this->p->app;

		// Get line endings
		$lnEnd = $this->p->app->getDocument()->_getLineEnd();
		$tab = $this->p->app->getDocument()->_getTab();
		$tagEnd = ' />';
		$buffer = '';

		// Generate stylesheet links
		foreach ($head['styleSheets'] as $strSrc => $strAttr) {
			$buffer .= $tab . '<link rel="stylesheet" href="' . $strSrc . '" type="' . $strAttr['mime'] . '"';
			if (!is_null($strAttr['media'])) {
				$buffer .= ' media="' . $strAttr['media'] . '" ';
			}
			if ($temp = ArrayHelper::toString($strAttr['attribs'])) {
				$buffer .= ' ' . $temp;
			}
			$buffer .= $tagEnd . $lnEnd;
		}

		// Generate stylesheet declarations
		foreach ($head['style'] as $type => $content) {
			$buffer .= $tab . '<style type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if (isset($document) && $document->_mime != 'text/html') {
				$buffer .= $tab . $tab . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if (isset($document) && $document->_mime != 'text/html') {
				$buffer .= $tab . $tab . $lnEnd;
			}
			$buffer .= $tab . '</style>' . $lnEnd;
		}

		// Generate script file links
		foreach ($head['scripts'] as $strSrc => $strAttr) {
			$buffer .= $tab . '<script src="' . $strSrc . '"';
			if (isset($strAttr['mime']) && !is_null($strAttr['mime'])) {
				$buffer .= ' type="' . ($strAttr['mime'] == 't' ? 'text/javascript' : $strAttr['mime']) . '"';
			}
			$buffer .= '></script>' . $lnEnd;
		}

		// Generate script declarations
		foreach ($head['script'] as $type => $content) {
			$buffer .= $tab . '<script type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if (isset($document) && $document->_mime != 'text/html') {
				$buffer .= $tab . $tab . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if (isset($document) && $document->_mime != 'text/html') {
				$buffer .= $tab . $tab . $lnEnd;
			}
			$buffer .= $tab . '</script>' . $lnEnd;
		}

		foreach ($head['custom'] as $custom) {
			$buffer .= $tab . $custom . $lnEnd;
		}

		return $buffer;
	}

	public function headers()
	{

		if ($this->hasFlashUpload) {
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/plupload/moxie.js');
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/plupload/plupload.js');
			$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-plupload-compat.js');
		}

		// loading system css
		$this->addStyleSheet(Uri::root(true) . '/components/com_breezingformsng/themes/quickmode/mobile-system.css');

		//$this->addScript(Uri::root(true) . '/media/vendor/jquery/js/jquery.min.js');
		//$this->addScript(Uri::root(true) . '/media/legacy/js/jquery-noconflict.min.js');

		$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/jq.min.legacy.js');

		if ($this->hasResponsiveDatePicker) {
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/picker.js');
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/picker.date.js');

			$lang = $this->p->app->getLanguage()->getTag();
			$lang = explode('-', $lang);
			$lang = strtolower($lang[0]);
			if (file_exists(JPATH_SITE . '/components/com_breezingformsng/libraries/jquery/pickadate/translations/' . $lang . '.js')) {
				$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/translations/' . $lang . '.js');
			}

			$this->addStyleSheet(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/themes/default.css');
			$this->addStyleSheet(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/pickadate/themes/default.date.css');
		}

		if (file_exists(JPATH_SITE . '/media/breezingforms/themes/jq.mobile.external-png.1.4.5.min.css')) {
			$this->addStyleSheet(Uri::root(true) . '/media/breezingforms/themes/jq.mobile.external-png.1.4.5.min.css');
		} else {
			$this->addStyleSheet(Uri::root(true) . '/media/breezingforms/themes/jq.mobile.1.4.5.icons.min.css');
		}

		$this->addStyleSheet(Uri::root(true) . '/media/breezingforms/themes/jq.mobile.1.4.5.min.css');

		if (file_exists(JPATH_SITE . '/media/breezingforms/themes/jq.mobile.1.4.5.custom.css')) {
			$this->addStyleSheet(Uri::root(true) . '/media/breezingforms/themes/jq.mobile.1.4.5.custom.css');
		}

		$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/jq.mobile.min.js');

		$this->addStyleSheet(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/tooltip.css');
		$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/tooltip.js');

		if ($this->hasFlashUpload) {
			$tickets = $this->p->app->getSession()->get('bfFlashUploadTickets', array());
			$tickets[$this->flashUploadTicket] = array(); // stores file info for later processing
			$this->p->app->getSession()->set('bfFlashUploadTickets', $tickets);
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/center.js');
			$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-flash-upload-mobile.js');
		}

		if ($this->useBalloonErrors) {
			$this->addStyleSheet(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/validationEngine.jquery.css');
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/jquery.validationEngine-en.js');
			$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/jquery/jquery.validationEngine.js');
		}

		$this->addStyleDeclaration('.tooltip { margin-left: 2%; margin-top: 5px; }');

		$toggleCode = '';
		if ($this->toggleFields != '[]') {
			$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-toggle-fields.js');
			$toggleCode = '
var toggleFieldsArray = ' . $this->toggleFields . ';
			';

		}

		$this->addScriptDeclaration(
			'
			var inlineErrorElements = new Array();
			var bfSummarizers = new Array();
			var bfDeactivateField = new Array();
			var bfDeactivateSection = new Array();
			var bfCharsLeftLabel = ' . json_encode(Text::_('COM_BREEZINGFORMSNG_CHARS_LEFT')) . ';
			' . $toggleCode . '
			'
		);

		$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-core-helpers-mobile.js');
		$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-deactivation.js');

		if (!$this->useErrorAlerts) {
			$showDefaultErrors = $this->useDefaultErrors || (!$this->useDefaultErrors && !$this->useBalloonErrors);
			$this->addScriptDeclaration(
				'var bfUseErrorAlerts = false;' . "\n"
				. 'var bfShowDefaultErrors = ' . ($showDefaultErrors ? 'true' : 'false') . ';' . "\n"
				. 'var bfErrorPageScoped = false;' . "\n"
			);
			$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-error-alerts-bootstrap.js');
		}
		$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-post-init-mobile.js');

	}

	public function addScript($script)
	{
		echo '<script type="text/javascript" src="' . $script . '"/>' . "\n" . '</script>' . "\n";
	}

	public function addStyleSheet($sheet)
	{
		echo '<link rel="stylesheet" href="' . $sheet . '" type="text/css" />' . "\n";
	}

	public function addScriptDeclaration($declaration)
	{
		echo '<script type="text/javascript"/><!--' . "\n" . $declaration . "\n" . '//--></script>' . "\n";
	}

	public function addStyleDeclaration($declaration)
	{
		echo '<style type="text/css">' . "\n" . $declaration . "\n" . '</style>' . "\n";
	}

	public function render()
	{

		echo '<div data-role="page" data-theme="a" class="ui-page ui-page-theme-a ui-page-active">';

		//  data-position="fixed"
		echo '<div data-role="header" class="ui-header ui-bar-inherit">';
		echo '<h1>' . $this->p->app->getDocument()->getTitle() . '</h1>';
		$current_url = Uri::getInstance()->toString();

		$return_url = $current_url;
		$return_url = (strstr($return_url, '?mobile=1') !== false ? str_replace('?mobile=1', '', $return_url) : str_replace('&mobile=1', '', $return_url));
		$return_url = $return_url . (strstr($return_url, '?') !== false ? '&' : '?') . 'non_mobile=1';
		echo '<a rel="external" href="' . ($this->forceMobileUrl != '' ? $this->forceMobileUrl : $return_url) . '" data-role="button" data-icon="back">' . Text::_('COM_BREEZINGFORMSNG_DESKTOP') . '</a>';
		echo '</div>';

		echo '<div data-role="content" class="ui-content ui-body-a" role="main">';

		$this->process($this->dataObject);
		echo '</div>' . "\n"; // closing last page

		if ($this->hasFlashUpload) {
			echo '<input type="hidden" name="bfFlashUploadTicket" value="' . $this->flashUploadTicket . '"/>' . "\n";
			echo "<div style=\"visibility:hidden;\" id=\"bfFileQueue\"></div>";
			echo "<div style=\"visibility:hidden;\" id=\"bfSubmitMessage\">" . Text::_('COM_BREEZINGFORMSNG_SUBMIT_MESSAGE') . "</div>";
		}

		echo '<noscript>Please turn on javascript to submit your data. Thank you!</noscript>' . "\n";

		echo '</div>';

		echo '</div>';

		echo '<input type="hidden" name="tmpl" value="component"/>';

	}

	public function process(&$dataObject, $parent = null, $parentPage = null, $index = 0, $childrenLength = 0)
	{
		if (isset($dataObject['attributes']) && isset($dataObject['properties'])) {

			$options = array('type' => 'normal', 'displayType' => 'breaks');
			if ($parent != null && $parent['type'] == 'section') {
				$options['type'] = $parent['bfType'];
				$options['displayType'] = $parent['displayType'];
			}

			$class = ' class="bfBlock"';
			$wrapper = 'bfWrapperBlock';
			if ($options['displayType'] == 'inline') {
				$class = ' class="bfInline"';
				$wrapper = 'bfWrapperInline';
			}

			$mdata = $dataObject['properties'];

			if ($mdata['type'] == 'page') {

				$parentPage = $mdata;
				if ($parentPage['pageNumber'] > 1) {
					echo '</div><!-- bfPage end -->' . "\n"; // closing previous pages
				}

				$display = ' style="display:none;"';
				if ($this->p->app->getInput()->getInt('ff_form_submitted', 0) == 0 && $this->p->app->getInput()->getInt('ff_page', 1) == $parentPage['pageNumber']) {
					$display = '';
				} else if ($this->p->app->getInput()->getInt('ff_form_submitted', 0) == 1 && $this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == count($this->dataObject['children'])) {
					$display = '';
				} else if ($this->p->app->getInput()->getInt('ff_form_submitted', 0) == 1 && false == $this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == 1) {
					$display = '';
				}

				echo '<div id="bfPage' . $parentPage['pageNumber'] . '" class="bfPage"' . $display . '>' . "\n"; // opening current page

				/* translatables */
				if (isset($mdata['pageIntro_translation' . $this->language_tag]) && $mdata['pageIntro_translation' . $this->language_tag] != '') {
					$mdata['pageIntro'] = $mdata['pageIntro_translation' . $this->language_tag];
				}
				/* translatables end */

				if (trim($mdata['pageIntro']) != '') {

					echo '<div class="bfPageIntro">' . "\n";

					$regex = '/{loadposition\s+(.*?)}/i';
					$introtext = $mdata['pageIntro'];

					preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);

					$document = $this->p->app->getDocument();
					$renderer = $document->loadRenderer('modules');
					$options = array('style' => 'xhtml');

					foreach ($matches as $match) {

						$matcheslist = explode(',', $match[1]);
						$position = trim($matcheslist[0]);
						$output = $renderer->render($position, $options, null);
						$introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
					}

					echo $introtext . "\n";

					echo '</div><div style="padding-bottom: 10px;"></div>' . "\n";
				}

				if (!$this->useErrorAlerts) {
					echo '<span class="bfErrorMessage" style="color: red; display:none;"></span>' . "\n";
				}

			} else if ($mdata['type'] == 'section') {

				if (isset($dataObject['properties']['name']) && isset($mdata['off']) && $mdata['off']) {
					$this->addScriptDeclaration(
						'bfRegisterDeactivatedSection(' . json_encode($dataObject['properties']['name']) . ');'
					);
				}

				/* translatables */
				if (isset($mdata['title_translation' . $this->language_tag]) && $mdata['title_translation' . $this->language_tag] != '') {
					$mdata['title'] = $mdata['title_translation' . $this->language_tag];
				}
				/* translatables end */

				if ($mdata['bfType'] == 'section') {
					echo '<div data-theme="a" data-role="collapsible-set"' . (isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '') . (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="' . $dataObject['properties']['name'] . '"' : '') . '><div data-role="collapsible" data-collapsed="false">' . "\n";
					if (trim($mdata['title']) != '') {
						echo '<h3>' . htmlentities(trim($mdata['title']), ENT_QUOTES, 'UTF-8') . '</h3>' . "\n";
					}
				} else if ($mdata['bfType'] == 'normal') {
					if (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != '') {
						echo '<div ' . (isset($mdata['off']) && $mdata['off'] ? 'style="display:none" ' : '') . 'class="bfNoSection"' . (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="' . $dataObject['properties']['name'] . '"' : '') . '>' . "\n";
					}
				}

				/* translatables */
				if (isset($mdata['description_translation' . $this->language_tag]) && $mdata['description_translation' . $this->language_tag] != '') {
					$mdata['description'] = $mdata['description_translation' . $this->language_tag];
				}
				/* translatables end */

				if (trim($mdata['description']) != '') {
					echo '<div>' . "\n";

					$regex = '/{loadposition\s+(.*?)}/i';
					$introtext = $mdata['description'];

					preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);

					if ($matches) {

						$document = $this->p->app->getDocument();
						$renderer = $document->loadRenderer('modules');
						$options = array('style' => 'xhtml');

						foreach ($matches as $match) {

							$matcheslist = explode(',', $match[1]);
							$position = trim($matcheslist[0]);
							$output = $renderer->render($position, $options, null);
							$introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
						}
					}

					echo $introtext . "\n";
					echo '</div><div style="padding-bottom: 10px;"></div>' . "\n";
				}

			} else if ($mdata['type'] == 'element') {

				//echo '<div class="bfElemWrap"'.(isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '').'>';

				// if labels left
				if (true) {
					echo '<div' . (isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '') . ' id="fieldcontain' . $mdata['bfName'] . '" class="bfElemWrap" data-role="fieldcontain">';
				}

				$onclick = '';
				if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
					$onclick = 'onclick="' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
				}

				$onblur = '';
				if (isset($mdata['actionBlur']) && $mdata['actionBlur'] == 1) {
					$onblur = 'onblur="' . $mdata['actionFunctionName'] . '(this,\'blur\');" ';
				}

				$onchange = '';
				if (isset($mdata['actionChange']) && $mdata['actionChange'] == 1) {
					$onchange = 'onchange="' . $mdata['actionFunctionName'] . '(this,\'change\');" ';
				}

				$onfocus = '';
				if (isset($mdata['actionFocus']) && $mdata['actionFocus'] == 1) {
					$onfocus = 'onfocus="' . $mdata['actionFunctionName'] . '(this,\'focus\');" ';
				}

				$onselect = '';
				if (isset($mdata['actionSelect']) && $mdata['actionSelect'] == 1) {
					$onselect = 'onselect="' . $mdata['actionFunctionName'] . '(this,\'select\');" ';
				}

				$legend = '';

				if (!$mdata['hideLabel'] && $mdata['bfType'] != 'bfPayPal' && $mdata['bfType'] != 'bfSofortueberweisung') {

					if (!($mdata['bfType'] == 'bfReCaptcha' && isset($mdata['invisibleCaptcha']) && $mdata['invisibleCaptcha'])) {

						$maxlengthCounter = '';
						if ($mdata['bfType'] == 'bfTextarea' && isset($mdata['maxlength']) && $mdata['maxlength'] > 0 && isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter']) {
							$maxlengthCounter = ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter' . $mdata['dbId'] . '***>(' . $mdata['maxlength'] . ' ' . Text::_('COM_BREEZINGFORMSNG_CHARS_LEFT') . ')</span>';
						}

						$for = '';
						if (
							$mdata['bfType'] == 'bfTextfield' ||
							$mdata['bfType'] == 'bfNumberInput' ||
							$mdata['bfType'] == 'bfTextarea' ||
							$mdata['bfType'] == 'bfCheckbox' ||
							$mdata['bfType'] == 'bfCheckboxGroup' ||
							$mdata['bfType'] == 'bfCalendar' ||
							$mdata['bfType'] == 'bfCalendarReponsive' ||
							$mdata['bfType'] == 'bfSelect' ||
							$mdata['bfType'] == 'bfRadioGroup' ||
							($mdata['bfType'] == 'bfFile' && ((!isset($mdata['flashUploader']) && !isset($mdata['html5'])) || (isset($mdata['flashUploader']) && !$mdata['flashUploader']) && (isset($mdata['html5']) && !$mdata['html5'])))
						) {
							$for = 'for="ff_elem' . $mdata['dbId'] . '"';
						}

						if ($mdata['bfType'] == 'bfCaptcha' || $mdata['bfType'] == 'bfReCaptcha') {
							$for = 'for="bfCaptchaEntry"';
						} else if ($mdata['bfType'] == 'bfReCaptcha') {
							$for = 'for="recaptcha_response_field"';
						}

						$req = '';
						if ($mdata['required']) {
							$req = '<span class="bfRequired"> * </span>' . "\n";
						}

						/* translatables */
						if (isset($mdata['label_translation' . $this->language_tag]) && $mdata['label_translation' . $this->language_tag] != '') {
							$mdata['label'] = $mdata['label_translation' . $this->language_tag];
						}
						/* translatables end */

						$labelText = trim($mdata['label']) . $req . str_replace("***", "\"", $maxlengthCounter);

						if (true && ($mdata['bfType'] == 'bfCheckboxGroup' || $mdata['bfType'] == 'bfRadioGroup')) {
							$legend = '<legend id="bfLabel' . $mdata['dbId'] . '">' . str_replace("***", "\"", $labelText) . '</legend>' . "\n";
						} else if ($mdata['bfType'] == 'bfSummarize') {
							$legend = $labelText;
						} else {
							echo '<label id="bfLabel' . $mdata['dbId'] . '" ' . $for . '>' . str_replace("***", "\"", $labelText) . '</label>' . "\n";
						}

					}
				}

				$readonly = '';
				if (isset($mdata['readonly']) && $mdata['readonly']) {
					$readonly = 'readonly="readonly" ';
				}

				$tabIndex = '';
				if ($mdata['tabIndex'] != -1 && is_numeric($mdata['tabIndex'])) {
					$tabIndex = 'tabindex="' . intval($mdata['tabIndex']) . '" ';
				}

				for ($i = 0; $i < $this->p->rowcount; $i++) {
					$row = $this->p->rows[$i];
					if ($mdata['bfName'] == $row->name) {

						if (
							(isset($mdata['value']) || isset($mdata['list']) || isset($mdata['group'])) &&
							(
								$mdata['bfType'] == 'bfTextfield' ||
								$mdata['bfType'] == 'bfTextarea' ||
								$mdata['bfType'] == 'bfCheckbox' ||
								$mdata['bfType'] == 'bfCheckboxGroup' ||
								$mdata['bfType'] == 'bfSubmitButton' ||
								$mdata['bfType'] == 'bfHidden' ||
								$mdata['bfType'] == 'bfCalendar' ||
								$mdata['bfType'] == 'bfNumberInput' ||
								$mdata['bfType'] == 'bfCalendarResponsive' ||
								$mdata['bfType'] == 'bfSelect' ||
								$mdata['bfType'] == 'bfRadioGroup'
							)
						) {

							if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
								$mdata['value_translation' . $this->language_tag] = $this->p->replaceCode($mdata['value_translation' . $this->language_tag], "data1 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							}

							if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
								$mdata['group_translation' . $this->language_tag] = $this->p->replaceCode($mdata['group_translation' . $this->language_tag], "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							}

							if (isset($mdata['list_translation' . $this->language_tag]) && $mdata['list_translation' . $this->language_tag] != '') {
								$mdata['list_translation' . $this->language_tag] = $this->p->replaceCode($mdata['list_translation' . $this->language_tag], "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							}

							if ($mdata['bfType'] == 'bfSelect') {
								$mdata['list'] = $this->p->replaceCode($row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							} else if ($mdata['bfType'] == 'bfCheckboxGroup' || $mdata['bfType'] == 'bfRadioGroup') {
								$mdata['group'] = $this->p->replaceCode($row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							} else {
								$mdata['value'] = $this->p->replaceCode($row->data1, "data1 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							}
						}
						if (isset($mdata['checked']) && $mdata['bfType'] == 'bfCheckbox') {
							$mdata['checked'] = $row->flag1 == 1 ? true : false;
						}
						break;
					}
				}

				$flashUploader = '';

				switch ($mdata['bfType']) {

					case 'bfTextfield':
						$type = 'text';

						if ($mdata['password']) {
							$type = 'password';
						}
						$maxlength = '';
						if (is_numeric($mdata['maxLength'])) {
							$maxlength = 'maxlength="' . intval($mdata['maxLength']) . '" ';
						}

						/* translatables */
						if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
							$mdata['value'] = $mdata['value_translation' . $this->language_tag];
						}

						if (isset($mdata['placeholder_translation' . $this->language_tag]) && $mdata['placeholder_translation' . $this->language_tag] != '') {
							$mdata['placeholder'] = $mdata['placeholder_translation' . $this->language_tag];
						}
						/* translatables end */

						echo '<input ' . (isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="' . htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8') . '" ' : '') . 'class="ff_elem" ' . $tabIndex . $maxlength . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
						if ($mdata['mailbackAsSender']) {
							echo '<input type="hidden" name="mailbackSender[' . $mdata['bfName'] . ']" value="true"/>' . "\n";
						}
						break;

					case 'bfNumberInput':
						$type = 'number';

						if ($mdata['range']) {
							$type = 'range';
						}

						$maxlength = '';
						if (is_numeric($mdata['maxLength'])) {
							$maxlength = 'maxlength="' . intval($mdata['maxlength']) . '" ';
						}

						/* translatables */
						if (isset($mdata['placeholder_translation' . $this->language_tag]) && $mdata['placeholder_translation' . $this->language_tag] != '') {
							$mdata['placeholder'] = $mdata['placeholder_translation' . $this->language_tag];
						}
						/* translatables end */

						echo '<input ' . (isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="' . htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8') . '" ' : '') . 'class="ff_elem" ' . $tabIndex . $maxlength . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
						break;

					case 'bfTextarea':
						$width = '';
						if ($mdata['width'] != '') {
							$width = 'width:' . htmlentities(strip_tags($mdata['width'])) . ' !important; min-width:' . htmlentities(strip_tags($mdata['width'])) . ' !important;';
						}
						$height = '';
						if ($mdata['height'] != '') {
							$height = 'height:' . htmlentities(strip_tags($mdata['height'])) . ';';
						}
						$size = '';
						if ($height != '' || $width != '') {
							$size = 'style="' . $width . $height . '" ';
						}
						$onkeyup = '';
						if (isset($mdata['maxlength']) && $mdata['maxlength'] > 0) {
							$onkeyup = 'onkeyup="bfCheckMaxlength(' . intval($mdata['dbId']) . ', ' . intval($mdata['maxlength']) . ', ' . (isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter'] ? 'true' : 'false') . ')" ';
						}
						/* translatables */
						if (isset($mdata['placeholder_translation' . $this->language_tag]) && $mdata['placeholder_translation' . $this->language_tag] != '') {
							$mdata['placeholder'] = $mdata['placeholder_translation' . $this->language_tag];
						}
						if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
							$mdata['value'] = $mdata['value_translation' . $this->language_tag];
						}
						/* translatables end */
						echo '<textarea ' . (isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="' . htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8') . '" ' : '') . ' class="ff_elem" ' . $onkeyup . $size . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '">' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '</textarea>' . "\n";
						break;

					case 'bfRadioGroup':
						/* translatables */
						if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
							$mdata['group'] = $mdata['group_translation' . $this->language_tag];
						}
						/* translatables end */
						if ($mdata['group'] != '') {

							$mdata['group'] = str_replace("\r", '', $mdata['group']);
							$gEx = explode("\n", $mdata['group']);
							$lines = count($gEx);

							$wrapOpen = '<div data-role="fieldcontain">' . "\n" . '<fieldset ' . ($lines <= 3 ? 'data-type="horizontal" ' : '') . 'data-role="controlgroup">' . $legend . "\n";
							$wrapClose = '</fieldset>' . "\n" . '</div>' . "\n";

							echo $wrapOpen;
							for ($i = 0; $i < $lines; $i++) {
								$idExt = $i != 0 ? '_' . $i : '';
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if ($iCnt == 3) {
									$lblRight = '<label class="bfGroupLabel" id="bfGroupLabel' . $mdata['dbId'] . $idExt . '" for="ff_elem' . $mdata['dbId'] . $idExt . '">' . trim($iEx[1]) . '</label>';
									echo '<input ' . ($iEx[0] == 1 ? 'checked="checked" ' : '') . ' class="ff_elem" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="radio" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . $idExt . '"/>' . $lblRight . "\n";
								}
							}
							echo $wrapClose;
						}

						break;

					case 'bfCheckboxGroup':
						/* translatables */
						if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
							$mdata['group'] = $mdata['group_translation' . $this->language_tag];
						}
						/* translatables end */
						if ($mdata['group'] != '') {

							$mdata['group'] = str_replace("\r", '', $mdata['group']);
							$gEx = explode("\n", $mdata['group']);
							$lines = count($gEx);

							$wrapOpen = '<div data-role="fieldcontain">' . "\n" . '<fieldset data-role="controlgroup">' . $legend . "\n";
							$wrapClose = '</fieldset>' . "\n" . '</div>' . "\n";

							echo $wrapOpen;
							for ($i = 0; $i < $lines; $i++) {
								$idExt = $i != 0 ? '_' . $i : '';
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if ($iCnt == 3) {
									$lbl = '<label class="bfGroupLabel" id="bfGroupLabel' . $mdata['dbId'] . $idExt . '" for="ff_elem' . $mdata['dbId'] . $idExt . '">' . trim($iEx[1]) . '</label>';
									echo '<input ' . ($iEx[0] == 1 ? 'checked="checked" ' : '') . ' class="ff_elem" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="checkbox" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . $idExt . '"/>' . $lbl . "\n";
								}
							}
							echo $wrapClose;

						}

						break;

					case 'bfCheckbox':

						echo '<input class="ff_elem" ' . ($mdata['checked'] ? 'checked="checked" ' : '') . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="checkbox" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
						if ($mdata['mailbackAccept']) {
							echo '<input type="hidden" class="ff_elem" name="mailbackConnectWith[' . $mdata['mailbackConnectWith'] . ']" value="true_' . $mdata['bfName'] . '"/>' . "\n";
						}
						break;

					case 'bfSelect':
						/* translatables */
						if (isset($mdata['list_translation' . $this->language_tag]) && $mdata['list_translation' . $this->language_tag] != '') {
							$mdata['list'] = $mdata['list_translation' . $this->language_tag];
						}
						/* translatables end */
						if ($mdata['list'] != '') {

							$mdata['list'] = str_replace("\r", '', $mdata['list']);
							$gEx = explode("\n", $mdata['list']);
							$lines = count($gEx);
							// data-native-menu="false"
							echo '<select class="ff_elem" ' . ($mdata['multiple'] ? 'multiple="multiple" data-native-menu="false" ' : '') . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '">' . "\n";
							for ($i = 0; $i < $lines; $i++) {
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if ($iCnt == 3) {
									echo '<option ' . ($iEx[0] == 1 ? 'selected="selected" ' : '') . 'value="' . htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8') . '">' . htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
								}
							}
							echo '</select>' . "\n";
						}

						break;

					case 'bfFile':
						if ((isset($mdata['flashUploader']) && $mdata['flashUploader']) || (isset($mdata['html5']) && $mdata['html5'])) {

							$base = explode('/', Uri::base());
							if (isset($base[count($base) - 2]) && $base[count($base) - 2] == 'administrator') {
								unset($base[count($base) - 2]);
								$base = array_merge($base);
							}
							$base = implode('/', $base);

							echo '<input type="hidden" id="flashUpload' . $mdata['bfName'] . '" name="flashUpload' . $mdata['bfName'] . '" value="bfFlashFileQueue' . $mdata['dbId'] . '"/>' . "\n";
							$this->hasFlashUpload = true;
							//allowedFileExtensions
							$allowedExts = explode(',', $mdata['allowedFileExtensions']);
							$allowedExtsCnt = count($allowedExts);
							for ($i = 0; $i < $allowedExtsCnt; $i++) {
								$allowedExts[$i] = $allowedExts[$i];
							}
							$exts = '';
							if ($allowedExtsCnt != 0) {
								$exts = implode(',', $allowedExts);
							}
							$bytes = (isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? "max_file_size : '" . intval($mdata['flashUploaderBytes']) . "'," : '');
							$flashUploader = "
                                                        <label id=\"bfUploadContainer" . $mdata['dbId'] . "\">
							<img style=\"cursor: pointer;\" id=\"bfPickFiles" . $mdata['dbId'] . "\" src=\"" . $this->uploadImagePath . "\" border=\"0\" width=\"" . (isset($mdata['flashUploaderWidth']) && is_numeric($mdata['flashUploaderWidth']) && $mdata['flashUploaderWidth'] > 0 ? intval($mdata['flashUploaderWidth']) : '64') . "\" height=\"" . (isset($mdata['flashUploaderHeight']) && is_numeric($mdata['flashUploaderHeight']) && $mdata['flashUploaderHeight'] > 0 ? intval($mdata['flashUploaderHeight']) : '64') . "\"/>
                                                            <div id=\"bfPickFiles" . $mdata['dbId'] . "holder\" style=\"display:none;\">&nbsp;</div>
                                                        </label>
                                                        <span id=\"bfUploader" . $mdata['bfName'] . "\"></span>
                                                        <div class=\"bfFlashFileQueueClass\" id=\"bfFlashFileQueue" . $mdata['dbId'] . "\"></div>
                                                        <script type=\"text/javascript\">
                                                        <!--
							bfFlashUploaders.push('ff_elem" . $mdata['dbId'] . "');
                                                        var bfFlashFileQueue" . $mdata['dbId'] . " = {};
                                                        function bfUploadImageThumb(file) {
                                                                var img;
                                                                var thumbId = '#' + file.id + 'thumb';
                                                                var thumbEl = JQuery(thumbId).get(0);

                                                                function bfIsImage(f) {
                                                                        var name = (f && f.name) ? f.name : '';
                                                                        var ext = name.split('.').pop().toLowerCase();
                                                                        if (f && f.type && f.type.indexOf('image/') === 0) {
                                                                                return true;
                                                                        }
                                                                        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].indexOf(ext) !== -1;
                                                                }

                                                                function bfFallbackThumb() {
                                                                        if (!thumbEl || !bfIsImage(file) || !window.FileReader) {
                                                                                return;
                                                                        }
                                                                        var nativeFile = null;
                                                                        if (file && typeof file.getNative === 'function') {
                                                                                nativeFile = file.getNative();
                                                                        }
                                                                        if (!nativeFile && file && typeof file.getSource === 'function') {
                                                                                var src = file.getSource();
                                                                                if (src && typeof src.getSource === 'function') {
                                                                                        nativeFile = src.getSource();
                                                                                }
                                                                        }
                                                                        if (!nativeFile) {
                                                                                return;
                                                                        }
                                                                        var reader = new FileReader();
                                                                        reader.onload = function(e) {
                                                                                try {
                                                                                        var imgTag = new Image();
                                                                                        imgTag.onload = function() {
                                                                                                imgTag.style.maxWidth = '100px';
                                                                                                imgTag.style.maxHeight = '60px';
                                                                                                thumbEl.innerHTML = '';
                                                                                                thumbEl.appendChild(imgTag);
                                                                                        };
                                                                                        imgTag.src = e.target.result;
                                                                                } catch (err) {}
                                                                        };
                                                                        reader.readAsDataURL(nativeFile);
                                                                }

                                                                if (window.moxie && window.moxie.image && window.moxie.image.Image && thumbEl) {
                                                                        try {
                                                                                img = new moxie.image.Image;
                                                                                img.onload = function() {
                                                                                        img.embed(thumbEl, {
                                                                                                width: 100,
                                                                                                height: 60,
                                                                                                crop: true,
                                                                                                swf_url: moxie.core.utils.Url.resolveUrl('" . $base . "components/com_breezingformsng/libraries/jquery/plupload/Moxie.swf')
                                                                                        });
                                                                                };

                                                                                img.onembedded = function() {
                                                                                        img.destroy();
                                                                                };

                                                                                img.onerror = function() {
                                                                                        bfFallbackThumb();
                                                                                };

                                                                                img.load(file.getSource());
                                                                                return;
                                                                        } catch (e) {}
                                                                }

                                                                bfFallbackThumb();
                                                        }
                                                        JQuery(document).ready(
                                                            function() {
                                                                var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/i) ? true : false );
                                                                var uploader = new plupload.Uploader({
                                                                        max_retries: 10,
                                                                        multi_selection: " . (isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false') . ",
                                                                        unique_names: iOS,
                                                                        chunk_size: '10kb',
                                                                        runtimes : '" . (isset($mdata['html5']) && $mdata['html5'] ? 'html5,' : '') . (isset($mdata['flashUploader']) && $mdata['flashUploader'] ? 'flash,' : '') . "html4',
                                                                        browse_button : 'bfPickFiles" . $mdata['dbId'] . "',
                                                                        container: 'bfUploadContainer" . $mdata['dbId'] . "',
                                                                        file_data_name: 'Filedata',
                                                                        multipart_params: { form: " . $this->p->form . ", itemName : '" . $mdata['bfName'] . "', bfFlashUploadTicket: '" . $this->flashUploadTicket . "', option: 'com_breezingformsng', format: 'html', flashUpload: 'true', Itemid: 0 },
                                                                        url : '" . $base . ($this->p->app->getConfig()->get('sef') && !$this->p->app->getConfig()->get('sef_rewrite') ? 'index.php/' : '') . ($this->p->app->getInput()->getCmd('lang', '') && $this->p->app->getConfig()->get('sef') ? ($this->p->app->getConfig()->get('sef_rewrite') ? 'index.php' : '') : 'index.php') . "',
                                                                        flash_swf_url : '" . $base . "components/com_breezingformsng/libraries/jquery/plupload/Moxie.swf',
                                                                        filters : [
                                                                                {title : '" . addslashes(Text::_('COM_BREEZINGFORMSNG_CHOOSE_FILE')) . "', extensions : '" . $exts . "'}
                                                                        ]
                                                                });
                                                                uploader.bind('FilesAdded', function(up, files) {
                                                                        for (var i in files) {
                                                                                if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                    var fsize = '';
                                                                                    if(typeof files[i].size != 'undefined'){
                                                                                        fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                    }
                                                                                    if(typeof bfUploadFileAdded == 'function'){
                                                                                        bfUploadFileAdded(files[i]);
                                                                                    }
                                                                                    JQuery('#bfFileQueue').append( '<div id=\"' + files[i].id + 'queue\">' + (iOS ? '' : files[i].name.replace(/[/\\?%*:|\"<>]/g, '')) + ' '+fsize+'<b></b></div>' );
                                                                                }
                                                                        }
                                                                        for (var i in files) {
                                                                            if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                var error = false;
                                                                                var fsize = '';
                                                                                if(typeof files[i].size != 'undefined'){
                                                                                    fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                }
                                                                                JQuery('#bfFlashFileQueue" . $mdata['dbId'] . "').append('<div class=\"bfFileQueueItem\" id=\"' + files[i].id + 'queueitem\"><div id=\"' + files[i].id + 'thumb\"></div><div id=\"' + files[i].id + '\"><img id=\"' + files[i].id + 'cancel\" src=\"" . $this->cancelImagePath . "\" style=\"cursor: pointer; padding-right: 10px;\" border=\"0\"/>' + (iOS ? '' : files[i].name.replace(/[/\\?%*:|\"<>]/g, '')) + ' ' + fsize + '<b id=\"' + files[i].id + 'msg\" style=\"color:red;\"></b></div></div>');
                                                                                var file_ = files[i];
                                                                                var uploader_ = uploader;
                                                                                var bfUploaders_ = bfUploaders;
                                                                                JQuery('#' + files[i].id + 'cancel').click(
                                                                                    function(){
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].stop();
                                                                                        }
                                                                                        var id_ = this.id.split('cancel');
                                                                                        id_ = id_[0];
                                                                                        uploader_.removeFile(id_);
                                                                                        JQuery('#'+id_+'queue').remove();
                                                                                        JQuery('#'+id_+'queueitem').remove();
                                                                                        bfFlashUploadersLength--;
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].start();
                                                                                        }
                                                                                        // re-enable button if there is none left
                                                                                        if( " . (isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false') . " == false ){
                                                                                            var the_size = JQuery('#bfFlashFileQueue" . $mdata['dbId'] . " .bfFileQueueItem').size();
                                                                                            if( the_size == 0 ){
                                                                                                JQuery('#bfPickFiles" . $mdata['dbId'] . "').css('display','block');
                                                                                                JQuery('#bfPickFiles" . $mdata['dbId'] . "holder').css('display','none');
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                );
                                                                                var thebytes = " . (isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? intval($mdata['flashUploaderBytes']) : '0') . ";
                                                                                if(thebytes > 0 && typeof files[i].size != 'undefined' && files[i].size > thebytes){
                                                                                     alert(' " . addslashes(Text::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_TOO_LARGE')) . "');
                                                                                     error = true;
                                                                                }
                                                                                var ext = files[i].name.replace(/[/\\?%*:|\"<>]/g, '').split('.').pop().toLowerCase();
                                                                                var exts = '" . strtolower($exts) . "'.split(',');
                                                                                var found = 0;
                                                                                for (var x in exts){
                                                                                    if(exts[x] == ext){
                                                                                        found++;
                                                                                    }
                                                                                }
                                                                                if(found == 0){
                                                                                    alert( ' " . addslashes(Text::_('COM_BREEZINGFORMSNG_FILE_EXTENSION_NOT_ALLOWED')) . "' );
                                                                                    error = true;
                                                                                }
                                                                                if(error){
                                                                                    JQuery('#'+files[i].id+'queue').remove();
                                                                                    JQuery('#'+files[i].id+'queueitem').remove();
                                                                                }else{
                                                                                    bfFlashUploadersLength++;
                                                                                }
                                                                                bfUploadImageThumb(files[i]);
                                                                            }
                                                                        }
                                                                        // disable the button if no multi upload
                                                                        if( " . (isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false') . " == false ){
                                                                            var the_size = JQuery('#bfFlashFileQueue" . $mdata['dbId'] . " .bfFileQueueItem').size();
                                                                            if( the_size > 0 ){
                                                                                JQuery('#bfPickFiles" . $mdata['dbId'] . "').css('display','none');
                                                                                JQuery('#bfPickFiles" . $mdata['dbId'] . "holder').css('display','block');
                                                                            }
                                                                        }
                                                                });
                                                                uploader.bind('UploadProgress', function(up, file) {
                                                                    if(typeof JQuery('#'+file.id+'queue').get(0) != 'undefined'){
                                                                        JQuery('#'+file.id+'queue').get(0).getElementsByTagName('b')[0].innerHTML = file.percent + '% <div style=\"height: 5px;width: ' + (file.percent*1.5) + 'px;background-color: #9de24f;\"></div>';
                                                                    }
                                                                });
                                                                uploader.bind('FileUploaded', function(up, file, response) {
                                                                    if(response.response!=''){
                                                                        if(response.response !== null){
                                                                            alert(response.response);
                                                                        }
                                                                    }
                                                                    JQuery('#'+file.id+'queue').remove();
                                                                });
                                                                uploader.init();
                                                                bfUploaders.push(uploader);
                                                            });
							//-->
                                                        </script>
							";
							// on mobiles, file uploads are forced not to be mandatory, since we cannot determin safely for all handsets if they are even allowed
							$this->addScriptDeclaration(
								'bfRegisterNonMobileFileField(' . json_encode($mdata['bfName']) . ');'
							);

							echo '<input class="ff_elem" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
							echo '<div style="clear: both;"></div>';
						} else {
							echo '<input class="ff_elem" ' . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="file" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
						}
						if ($mdata['attachToAdminMail']) {
							echo '<input type="hidden" name="attachToAdminMail[' . $mdata['bfName'] . ']" value="true"/>' . "\n";
						}
						if ($mdata['attachToUserMail']) {
							echo '<input type="hidden" name="attachToUserMail[' . $mdata['bfName'] . ']" value="true"/>' . "\n";
						}
						break;

						// on mobiles, file uploads are forced not to be mandatory, since we cannot determin safely for all handsets if they are even allowed
						$this->addScriptDeclaration(
							'bfRegisterDeactivatedField(' . json_encode($mdata['bfName']) . ');'
						);

						break;

					case 'bfSubmitButton':

						/* translatables */
						if (isset($mdata['src_translation' . $this->language_tag]) && $mdata['src_translation' . $this->language_tag] != '') {
							$mdata['src'] = $mdata['src_translation' . $this->language_tag];
						}
						if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
							$mdata['value'] = $mdata['value_translation' . $this->language_tag];
						}
						/* translatables end */

						$value = '';
						$type = 'submit';
						$src = '';

						if ($mdata['src'] != '') {
							$type = 'image';
							$src = 'src="' . $mdata['src'] . '" ';
						}
						if ($mdata['value'] != '') {
							$value = 'value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" ';
						}
						if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
							$onclick = 'onclick="populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};' . $mdata['actionFunctionName'] . '(this,\'click\');return false;" ';
						} else {
							$onclick = 'onclick="populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};return false;" ';
						}
						if ($src == '') {
							echo '<button data-theme="a" class="ff_elem bfCustomSubmitButton" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"><span>' . $mdata['value'] . '</span></button>' . "\n";
						} else {
							echo '<input class="ff_elem bfCustomSubmitButton" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '" value="' . $mdata['value'] . '"/>' . "\n";
						}
						break;

					case 'bfHidden':

						echo '<input class="ff_elem" type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" value="' . htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8') . '" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";
						break;

					case 'bfSummarize':

						/* translatables */
						if (isset($mdata['emptyMessage_translation' . $this->language_tag]) && $mdata['emptyMessage_translation' . $this->language_tag] != '') {
							$mdata['emptyMessage'] = $mdata['emptyMessage_translation' . $this->language_tag];
						}
						/* translatables end */

						echo '<div class="ui-grid-a">
                                                            <div class="ui-block-a"><strong>' . $legend . '</strong></div>
                                                            <div class="ui-block-b ff_elem bfSummarize" id="ff_elem' . $mdata['dbId'] . '"></div>
                                                    </div>';
						echo '<script type="text/javascript">bfRegisterSummarize('
							. json_encode('ff_elem' . $mdata['dbId']) . ', '
							. json_encode($mdata['connectWith']) . ', '
							. json_encode($mdata['connectType']) . ', '
							. json_encode($mdata['emptyMessage']) . ', '
							. json_encode((bool) $mdata['hideIfEmpty']) . ');</script>';
						if (trim($mdata['fieldCalc']) != '') {
							echo '<script type="text/javascript">
                                                        <!--
							function bfFieldCalcff_elem' . $mdata['dbId'] . '(value){
								if(!isNaN(value)){
									value = Number(value);
								}
								' . $mdata['fieldCalc'] . '
								return value;
							}
                                                        //-->
							</script>';
						}
						break;

					case 'bfReCaptcha':

						if (isset($mdata['pubkey']) && $mdata['pubkey'] != '') {

							if (!isset($mdata['invisibleCaptcha']) || !$mdata['invisibleCaptcha']) {

								$http = 'https';

								$getLangTag = $this->p->app->getLanguage()->getTag();
								$getLangSlug = explode('-', $getLangTag);
								$reCaptchaLang = 'hl=' . $getLangSlug[0];

								$size = (isset($mdata['size']) && $mdata['size'] != '') ? $mdata['size'] : 'normal';

								$this->addScript($http . '://www.google.com/recaptcha/api.js?' . $reCaptchaLang . '&onload=onloadBFNewRecaptchaCallback&render=explicit', $type = "text/javascript", array('data-usercentrics' => 'reCAPTCHA'));
								$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-recaptcha-visible.js');

								echo '
                                                    <div style="display: inline-block !important; vertical-align: middle;">
                                                        <div id="newrecaptcha"></div>
                                                    </div>
                                                    <script data-usercentrics="reCAPTCHA" type="text/javascript">bfInitVisibleReCaptcha(' . json_encode([
									'sitekey' => $mdata['pubkey'],
									'theme' => trim($mdata['theme']) == '' ? 'light' : trim($mdata['theme']),
									'size' => $size,
									'resetOnRerender' => false,
								]) . ');</script>';

							} else
								if (isset($mdata['invisibleCaptcha']) && $mdata['invisibleCaptcha']) {

									$http = 'https';

									$this->addScript($http . '://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit', $type = "text/javascript", array('data-usercentrics' => 'reCAPTCHA'));
									$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-recaptcha-invisible-mobile.js');

									echo '<script data-usercentrics="reCAPTCHA" type="text/javascript">bfInitInvisibleReCaptchaMobile(' . json_encode([
										'sitekey' => $mdata['pubkey'],
										'theme' => trim($mdata['theme']) == '' ? 'light' : trim($mdata['theme']),
										'hasFlashUpload' => $this->hasFlashUpload,
										'dbId' => (int) $mdata['dbId'],
										'formId' => $this->p->form_id,
									]) . ');</script>';
								}
						} else {
							echo '<span class="bfCaptcha">' . "\n";
							echo 'WARNING: No public key given for ReCaptcha element!';
							echo '</span>' . "\n";
						}
						break;
					case 'bfCaptcha':

						$captcha_url = Uri::root(true)
							. ($this->p->app->isClient('administrator') ? '/administrator' : '')
							. '/index.php?option=com_breezingformsng&bfCaptcha=1';

						echo '<div class="ui-grid-a">';
						echo '<img alt="" border="0" width="230" id="ff_capimgValue" class="ff_capimg" src="' . $captcha_url . '"/><br/><br/>' . "\n";


						echo '<input autocomplete="off" class="ff_elem" type="text" name="bfCaptchaEntry" id="bfCaptchaEntry" />' . "\n";
						echo '<button data-role="button" data-icon="refresh" data-inline="true" data-iconpos="notext" data-theme="a" id="bfCaptchaReload" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();document.getElementById(\'ff_capimgValue\').src = \'' . $captcha_url . '&bfMathRandom=\' + Math.random(); return false"><span>Reload Captcha</span></button>';
						echo '</div>';
						break;

						case 'bfCalendar':

							/* translatables */
							if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
								$mdata['value'] = $mdata['value_translation' . $this->language_tag];
						}
						if (isset($mdata['format_translation' . $this->language_tag]) && $mdata['format_translation' . $this->language_tag] != '') {
							$mdata['format'] = $mdata['format_translation' . $this->language_tag];
						}
						$icon = '';
						if ($this->rootMdata['themebootstrapThemeEngine'] == 'bootstrap' && $this->rootMdata['themebootstrap'] == 'Azure') {
							if (!isset($mdata['icon']) || $mdata['icon'] == '') {
								$icon = '<i class="fas fa-calendar iconf--fumi" aria-hidden="true"></i>';
							} else {
									$icon = '<i class="fas ' . htmlentities($mdata['icon'], ENT_QUOTES, 'UTF-8') . ' iconf--fumi" aria-hidden="true"></i>';
								}
							}
							/* translatables end */
							$this->addStyleSheet(Uri::root(true) . '/media/system/css/fields/calendar.min.css');
							$this->addScript(Uri::root(true) . '/media/system/js/fields/calendar-locales/date/gregorian/date-helper.min.js');
							$this->addScript(Uri::root(true) . '/media/system/js/fields/calendar.min.js');
							$this->addScript(Uri::root(true) . '/media/vendor/bootstrap/js/bootstrap.bundle.min.js');
							$this->addScript(Uri::root(true) . '/media/system/js/core.min.js');
							$this->addScript(Uri::root(true) . '/media/legacy/js/bootstrap-init.min.js');

							echo '<div class="d-flex flex-wrap align-items-center gap-2">';
							echo '<div class="mb-0 other-form-group">';
							echo '<span class="nonform-control">';

							$size = '';
							if ($mdata['size'] != '') {
								$size = 'style="width:' . htmlentities(strip_tags($mdata['size'])) . '" ';
							}

							$exploded = explode('::', trim((string) $mdata['value']));
							$left = '';

							if (count($exploded) == 2) {
								$left = trim($exploded[0]);
							} elseif (count($exploded) == 1) {
								$left = trim($exploded[0]);

								if ($left === '...') {
									$left = '';
								}
							}

							// public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = array())
							$calAttr = [
								'class' => 'ff_elem bfCalendar',
								'showTime' => $this->bfCalendarShowTimeEnabled($mdata),
								'timeFormat' => $this->bfCalendarIsTruthy($mdata, 'timeFormat') ? '24' : '12',
								'singleHeader' => $this->bfCalendarIsTruthy($mdata, 'singleHeader'),
								'todayBtn' => $this->bfCalendarIsTruthy($mdata, 'todayButton'),
								'weekNumbers' => $this->bfCalendarIsTruthy($mdata, 'weekNumbers'),
								'minYear' => (isset($mdata['minYear']) && $mdata['minYear'] != '') ? '-' . $mdata['minYear'] : '',
								'maxYear' => (isset($mdata['maxYear']) && $mdata['maxYear'] != '') ? '+' . $mdata['maxYear'] : '',
								'firstDay' => (isset($mdata['firstDay']) && $mdata['firstDay'] != '') ? $mdata['firstDay'] : '7',
							];

							echo $this->calendar($left, "ff_nm_" . $mdata['bfName'] . "[]", "ff_elem" . $mdata['dbId'], $mdata['format'], $calAttr);

							$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-calendar-mobile.js');
							$this->addScriptDeclaration(
								'bfInitMobileCalendar(' . json_encode((int) $mdata['dbId']) . ', '
								. json_encode(Text::_('COM_BREEZINGFORMSNG_CALENDAR_OPEN')) . ');'
							);
							echo '</span>';
							echo '</div>';
							echo '</div>';
							break;

						case 'bfCalendarResponsive':

						/* translatables */
						if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
							$mdata['value'] = $mdata['value_translation' . $this->language_tag];
						}
							if (isset($mdata['format_translation' . $this->language_tag]) && $mdata['format_translation' . $this->language_tag] != '') {
								$mdata['format'] = $mdata['format_translation' . $this->language_tag];
							}
							/* translatables end */
							$mdata['format'] = $this->bfCalendarToPickadateFormat($mdata['format']);
							$pickerFirstDay = $this->bfCalendarToPickadateFirstDay(isset($mdata['firstDay']) ? $mdata['firstDay'] : '');
							$pickerSelectYears = $this->bfCalendarSelectYears($mdata);

							$exploded = explode('::', trim($mdata['value']));

						$left = '';
						$right = '';
							if (count($exploded) == 2) {
								$left = trim($exploded[0]);
								$right = trim($exploded[1]);
							} else {
								$right = trim($exploded[0]);
							}
							if ($right === '') {
								$right = '...';
							}

							echo '<input autocomplete="off" class="ff_elem" type="text" name="ff_nm_' . $mdata['bfName'] . '[]"  id="ff_elem' . $mdata['dbId'] . '" value="' . htmlentities($left, ENT_QUOTES, 'UTF-8') . '"/>' . "\n";
							echo '<label for="ff_elem' . $mdata['dbId'] . '_calendarButton"></label>';
							echo '<button data-theme="a" id="ff_elem' . $mdata['dbId'] . '_calendarButton" type="button" class="bfCalendar" value="' . htmlentities($right, ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities($right, ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";

						if (!$this->hasResponsiveDatePicker) {
							$this->p->app->getDocument()->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-calendar-responsive-init.js');
						}

						echo '<script type="text/javascript">bfInitCalendarResponsive(' . json_encode((int) $mdata['dbId']) . ', ' . json_encode([
							'format' => $mdata['format'],
							'selectYears' => $pickerSelectYears,
							'firstDay' => $pickerFirstDay,
							'hasYearScroller' => false,
						]) . ');</script>' . "\n";

						$this->hasResponsiveDatePicker = true;

						break;

					case 'bfSignature':

						$this->addScript(Uri::root(true) . '/components/com_breezingformsng/libraries/js/signature.js');
						$this->addScript(Uri::root(true) . '/media/com_breezingformsng/js/site/quickmode-signature.js');
						$this->addScriptDeclaration(
							'bfSignatureInit(' . json_encode((int) $mdata['dbId']) . ');'
						);

						echo '<div class="bfSignature" id="bfSignature' . $mdata['dbId'] . '"><div class="bfSignatureCanvasBorder"><canvas></canvas></div>' . "\n";
						echo '<button onclick="bfSignatureReset(' . json_encode((int) $mdata['dbId']) . ');" class="bfSignatureResetButton button btn btn-primary"><span>' . Text::_('COM_BREEZINGFORMSNG_SIGNATURE_RESET_BUTTON') . '</span></button>' . "\n";
						echo '</div>';
						echo '<input class="ff_elem" type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" value="" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";

						break;

					case 'bfStripe':

						/* translatables */
						if (isset($mdata['image_translation' . $this->language_tag]) && $mdata['image_translation' . $this->language_tag] != '') {
							$mdata['image'] = $mdata['image_translation' . $this->language_tag];
						}
						/* translatables end */

						$value = '';
						$type = 'submit';
						$src = '';
						if ($mdata['image'] != '') {
							$type = 'image';
							$src = 'src="' . $mdata['image'] . '" ';
						} else {
							$value = 'value="Stripe" ';
						}
						if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';" ';
						}
						echo '<div align="center"><input data-role="none" class="ff_elem" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/></div>' . "\n";
						break;

					case 'bfPayPal':

						/* translatables */
						if (isset($mdata['image_translation' . $this->language_tag]) && $mdata['image_translation' . $this->language_tag] != '') {
							$mdata['image'] = $mdata['image_translation' . $this->language_tag];
						}
						/* translatables end */

						$value = '';
						$type = 'submit';
						$src = '';
						if ($mdata['image'] != '') {
							$type = 'image';
							$src = 'src="' . $mdata['image'] . '" ';
						} else {
							$value = 'value="PayPal" ';
						}
						if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';" ';
						}
						echo '<div align="center"><input data-role="none" class="ff_elem" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/></div>' . "\n";
						break;

					case 'bfSofortueberweisung':

						/* translatables */
						if (isset($mdata['image_translation' . $this->language_tag]) && $mdata['image_translation' . $this->language_tag] != '') {
							$mdata['image'] = $mdata['image_translation' . $this->language_tag];
						}
						/* translatables end */

						$value = '';
						$type = 'submit';
						$src = '';
						if ($mdata['image'] != '') {
							$type = 'image';
							$src = 'src="' . $mdata['image'] . '" ';
						} else {
							$value = 'value="Sofortueberweisung" ';
						}
						if (isset($mdata['actionClick']) && $mdata['actionClick'] == 1) {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';' . $mdata['actionFunctionName'] . '(this,\'click\');" ';
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';" ';
						}
						echo '<div align="center"><input data-role="none" class="ff_elem" ' . $value . $src . $tabIndex . $onclick . $onblur . $onchange . $onfocus . $onselect . $readonly . 'type="' . $type . '" name="ff_nm_' . $mdata['bfName'] . '[]" id="ff_elem' . $mdata['dbId'] . '"/></div>' . "\n";
						break;
				}

				/* translatables */
				if (isset($mdata['hint_translation' . $this->language_tag]) && $mdata['hint_translation' . $this->language_tag] != '') {
					$mdata['hint'] = $mdata['hint_translation' . $this->language_tag];
				}
				/* translatables end */

				if (trim($mdata['hint']) != '') {
					$labid = uniqid();
					echo '<div><button class="bfTooltipButton" data-theme="a" onclick="JQuery(\'.tooltip\').hide(\'fast\');JQuery(\'#' . $labid . '_tip\').show(\'fast\');" data-role="button" data-icon="info" data-inline="true" data-iconpos="notext" id="' . $labid . '">' . trim($mdata['hint']) . '</button><span id="' . $labid . '_tip" class="tooltip">' . trim($mdata['hint']) . '</span></div>';
				}

				if (isset($mdata['bfName']) && isset($mdata['off']) && $mdata['off']) {
					$this->addScriptDeclaration(
						'bfRegisterDeactivatedField(' . json_encode($mdata['bfName']) . ');'
					);
				}

				if ($mdata['bfType'] == 'bfFile') {
					echo '<span id="ff_elem' . $mdata['dbId'] . '_files"></span>';
				}

				echo $flashUploader;

				// if labels left
				if (true) {
					echo '</div>';
				}

				//echo '</div>';
			}
		}

		/**
		 * Paging and wrapping of inline element containers
		 */
		if (isset($dataObject['children']) && count($dataObject['children']) != 0) {
			$childrenAmount = count($dataObject['children']);
			for ($i = 0; $i < $childrenAmount; $i++) {
				$this->process($dataObject['children'][$i], $mdata, $parentPage, $i, $childrenAmount);
			}
		}

		if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'section') {

			echo '</div></div>' . "\n";

		} else if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'normal') {
			if (isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != '') {
				echo '</div>' . "\n";
			}
		} else if (isset($dataObject['properties']) && $dataObject['properties']['type'] == 'page') {

			$isLastPage = false;
			if ($this->rootMdata['lastPageThankYou'] && $dataObject['properties']['pageNumber'] == count($this->dataObject['children']) && count($this->dataObject['children']) > 1) {
				$isLastPage = true;
			}

			if (!$isLastPage) {

				$last = 0;
				if ($this->rootMdata['lastPageThankYou']) {
					$last = 1;
				}

				if ($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] > 1) {
					/* translatables */
					if (isset($this->rootMdata['pagingPrevLabel_translation' . $this->language_tag]) && $this->rootMdata['pagingPrevLabel_translation' . $this->language_tag] != '') {
						$this->rootMdata['pagingPrevLabel'] = $this->rootMdata['pagingPrevLabel_translation' . $this->language_tag];
					}
					/* translatables end */
					echo '<button data-theme="a" class="bfPrevButton" type="submit" onclick="ff_validate_prevpage(this, \'click\');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="' . htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
				}

				if ($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] < count($this->dataObject['children']) - $last) {
					/* translatables */
					if (isset($this->rootMdata['pagingNextLabel_translation' . $this->language_tag]) && $this->rootMdata['pagingNextLabel_translation' . $this->language_tag] != '') {
						$this->rootMdata['pagingNextLabel'] = $this->rootMdata['pagingNextLabel_translation' . $this->language_tag];
					}
					/* translatables end */
					echo '<button data-theme="a" class="bfNextButton" type="submit" onclick="ff_validate_nextpage(this, \'click\');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="' . htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
				}

				if ($this->rootMdata['cancelInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last) {
					/* translatables */
					if (isset($this->rootMdata['cancelLabel_translation' . $this->language_tag]) && $this->rootMdata['cancelLabel_translation' . $this->language_tag] != '') {
						$this->rootMdata['cancelLabel'] = $this->rootMdata['cancelLabel_translation' . $this->language_tag];
					}
					/* translatables end */
					echo '<button data-theme="a" class="bfCancelButton" type="submit" onclick="ff_resetForm(this, \'click\');"  value="' . htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
				}

				$callSubmit = 'ff_validate_submit(this, \'click\')';
				if ($this->hasFlashUpload) {
					$callSubmit = 'if(typeof bfAjaxObject101 == \'undefined\' && typeof bfReCaptchaLoaded == \'undefined\'){bfDoFlashUpload()}else{ff_validate_submit(this, \'click\')}';
				}
				if ($this->rootMdata['submitInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last) {
					/* translatables */
					if (isset($this->rootMdata['submitLabel_translation' . $this->language_tag]) && $this->rootMdata['submitLabel_translation' . $this->language_tag] != '') {
						$this->rootMdata['submitLabel'] = $this->rootMdata['submitLabel_translation' . $this->language_tag];
					}
					/* translatables end */
					echo '<button data-theme="a" id="bfSubmitButton" class="bfSubmitButton" type="submit" onclick="if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};' . $callSubmit . ';" value="' . htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8') . '"><span>' . htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8') . '</span></button>' . "\n";
				}

			}
		}
	}

	public function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = array())
	{
		$tag = $this->p->app->getLanguage()->getTag();
		$calendar = $this->p->app->getLanguage()->getCalendar();
		$direction = strtolower($this->p->app->getDocument()->getDirection());

		// Get the appropriate file for the current language date helper
		$helperPath = Uri::root(true) . '/media/system/js/fields/calendar-locales/date/gregorian/date-helper.min.js';

		if (!empty($calendar) && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar))) {
			$helperPath = Uri::root(true) . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
		}

		// Get the appropriate locale file for the current language
		$localesPath = Uri::root(true) . '/media/system/js/fields/calendar-locales/en.js';

		if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js')) {
			$localesPath = Uri::root(true) . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js';
		} elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . $tag . '.js')) {
			$localesPath = Uri::root(true) . '/media/system/js/fields/calendar-locales/' . $tag . '.js';
		} elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js')) {
			$localesPath = Uri::root(true) . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
		}

		$this->addScript($localesPath);

		$readonly = isset($attribs['readonly']) && $attribs['readonly'] === 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] === 'disabled';
		$autocomplete = isset($attribs['autocomplete']) && $attribs['autocomplete'] === '';
		$autofocus = isset($attribs['autofocus']) && $attribs['autofocus'] === '';
		$required = isset($attribs['required']) && $attribs['required'] === '';
		$filter = isset($attribs['filter']) && $attribs['filter'] === '';
		$todayBtn = $attribs['todayBtn'] ?? true;
		$weekNumbers = $attribs['weekNumbers'] ?? true;
		$showTime = $attribs['showTime'] ?? false;
		$fillTable = $attribs['fillTable'] ?? true;
		$timeFormat = $attribs['timeFormat'] ?? 24;
		$singleHeader = $attribs['singleHeader'] ?? false;
		$hint = $attribs['placeholder'] ?? '';
		$class = $attribs['class'] ?? '';
		$onchange = $attribs['onChange'] ?? '';
		$minYear = $attribs['minYear'] ?? null;
		$maxYear = $attribs['maxYear'] ?? null;

		$showTime = ($showTime) ? "1" : "0";
		$todayBtn = ($todayBtn) ? "1" : "0";
		$weekNumbers = ($weekNumbers) ? "1" : "0";
		$fillTable = ($fillTable) ? "1" : "0";
		$singleHeader = ($singleHeader) ? "1" : "0";

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($value && $value !== $this->p->database->getNullDate() && strtotime($value) !== false) {
			$dateFormat = HTMLHelper::strftimeFormatToDateFormat($format);

			if ($dateFormat === false) {
				throw new \InvalidArgumentException('Unsupported calendar date format: ' . $format);
			}

			$inputvalue = (new \DateTimeImmutable($value, new \DateTimeZone('UTC')))->format($dateFormat);
		} else {
			$inputvalue = '';
		}

		$data = array(
			'id' => $id,
			'name' => $name,
			'class' => $class,
			'value' => $inputvalue,
			'format' => $format,
			'filter' => $filter,
			'required' => $required,
			'readonly' => $readonly,
			'disabled' => $disabled,
			'hint' => $hint,
			'autofocus' => $autofocus,
			'autocomplete' => $autocomplete,
			'todaybutton' => $todayBtn,
			'weeknumbers' => $weekNumbers,
			'showtime' => $showTime,
			'filltable' => $fillTable,
			'timeformat' => $timeFormat,
			'singleheader' => $singleHeader,
			'tag' => $tag,
			'helperPath' => $helperPath,
			'localesPath' => $localesPath,
			'direction' => $direction,
			'onchange' => $onchange,
			'minYear' => $minYear,
			'maxYear' => $maxYear,
			'dataAttribute' => '',
			'dataAttributes' => '',
		);

		return LayoutHelper::render('joomla.form.field.calendar', $data, null, null);
	}
}
