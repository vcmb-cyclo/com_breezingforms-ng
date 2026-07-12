<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

// Legacy table classes of the form engine (global names, used across the processor).
class facileFormsMenus extends Table
{
	public $id = null;     // identifier
	public $package = null;     // package name
	public $parent = 0;        // parent id
	public $ordering = 0;        // ordering
	public $published = 1;        // is published
	public $img = '';       // menu icon image
	public $title = '';       // displayed menu name
	public $name = '';       // form name (identifier)
	public $page = 1;        // starting page
	public $frame = 0;        // run in iframe
	public $border = 0;        // show a border
	public $params = null;     // additional parameters

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_compmenus', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$database->setQuery("select * from #__facileforms_compmenus where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val)
				if ($prop[0] != '_')
					$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsMenus

class facileFormsForms extends Table
{
	public $id = null;     // identifier
	public $package = null;     // package name
	public $ordering = null;     // ordering
	public $published = null;     // no/yes
	public $runmode = null;     // 0-any/1-foreground/2-background
	public $name = null;     // form name (identifier)
	public $title = null;     // fancy name
	public $description = null;     // form description
	public $class1 = null;     // css class for <div>
	public $class2 = null;     // css class for <form>
	public $width = null;     // form width in px
	public $widthmode = null;     // 0=px 1=%
	public $height = null;     // form height in px
	public $heightmode = null;     // 0=px 1=auto
	public $pages = null;     // # of pages
	public $emailntf = null;     // none/default/custom
	public $mb_emailntf = null;     // none/default/custom
	public $emaillog = null;     // header only/nonempty values/all
	public $mb_emaillog = null;     // header only/nonempty values/all
	public $emailxml = null;     // xml attachment no/nonempty values/all
	public $mb_emailxml = null;     // xml attachment no/nonempty values/all
	public $emailadr = null;     // custom email address
	public $dblog = null;     // no/nonempty values/all
	public $script1cond = null;     // init: none/library/custom
	public $script1id = null;     // library function id
	public $script1code = null;     // custom code ff_{form}_init()
	public $script2cond = null;     // submitted: none/library/custom
	public $script2id = null;     // library function id
	public $script2code = null;     // custom code ff_{form}_submitted(status='success','failed')
	public $piece1cond = null;     // Before form: none/library/custom
	public $piece1id = null;     // library function id
	public $piece1code = null;     // custom code
	public $piece2cond = null;     // After form: none/library/custom
	public $piece2id = null;     // library function id
	public $piece2code = null;     // custom code
	public $piece3cond = null;     // Begin submit: none/library/custom
	public $piece3id = null;     // library function id
	public $piece3code = null;     // custom code
	public $piece4cond = null;     // End submit: none/library/custom
	public $piece4id = null;     // library function id
	public $piece4code = null;     // custom code
	public $prevmode = null;     // preview mode 0-none 1-below 2-overlay
	public $prevwidth = null;     // preview width px in case of widthmode=1
		public $template_code_processed = null; // processed QuickMode template marker
	public $template_code = null;
	public $template_areas = null;
	public $custom_mail_subject = null;
	public $mb_custom_mail_subject = null;
	public $alt_mailfrom = null;
	public $mb_alt_mailfrom = null;
	public $alt_fromname = null;
	public $mb_alt_fromname = null;
	public $mailchimp_email_field = null;
	public $mailchimp_api_key = null;
	public $mailchimp_list_id = null;
	public $mailchimp_double_optin = null;
	public $mailchimp_mergevars = null;
	public $mailchimp_checkbox_field = null;
	public $mailchimp_text_html_mobile_field = null;
	public $mailchimp_send_errors = null;
	public $mailchimp_update_existing = null;
	public $mailchimp_replace_interests = null;
	public $mailchimp_send_welcome = null;
	public $mailchimp_default_type = null;
	public $mailchimp_unsubscribe_field = null;
	public $mailchimp_send_notify = null;
	public $mailchimp_send_goodbye = null;
	public $mailchimp_delete_member = null;
	public $salesforce_token = null;
	public $salesforce_username = null;
	public $salesforce_password = null;
	public $salesforce_type = null;
	public $salesforce_fields = null;
	public $salesforce_enabled = null;
	public $email_type = null;
	public $mb_email_type = null;
	public $email_custom_template = null;
	public $mb_email_custom_template = null;
	public $email_custom_html = null;
	public $mb_email_custom_html = null;
	public $dropbox_email = '';
	public $dropbox_password = '';
	public $dropbox_folder = '';
	public $dropbox_submission_enabled = 0;
	public $dropbox_submission_types = 'pdf';
	public $double_opt = '';
	public $opt_mail = '';
	public $created = null;
	public $created_by = null;
	public $modified = null;
	public $modified_by = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_forms', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_forms where id = $id");
		$rows = $database->loadObjectList();

		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val) {
				if ($prop[0] != '_') {
					@$this->$prop = $row->$prop;
				}
			}
			// Deprecated in PHP 7.2 version so code above is used

			// while (list($prop, $val) = each($arr))
			// 	if ($prop[0] != '_'){
			// 		@$this->$prop = $row->$prop;
			// 	}
			return true;
		} // if
		return false;
	} // load

} // class facileFormsForms

class facileFormsElements extends Table
{
	public $id = null;     // general parameters
	public $form = null;     // form id
	public $page = null;     // page number
	public $ordering = null;     // ordering index
	public $published = null;     // publish status
	public $name = null;     // identifier
	public $title = null;     // fancy name
	public $type = null;     // element type
/*
-----------------------------------------Element Parameter Cross Reference-------------------------------------------
Element             logging posx posy width height flag1    flag2    data1   data2     data3  script1 script2 script3
---------------------------------------------------------------------------------------------------------------------
Static Text/HTML    -       px%  px%  px%   px%    -        -        value   -         -      -       -       -
Rectangle           -       px%  px%  px%   px%    -        -        border  bckg.col. -      -       -       -
Image               -       px%  px%  px%   px%    -        -        img.url alt.text  -      -       -       -
Tooltip             -       px%  px%  -     -      type     -        img.url text      -      -       -       -
Regular Button      -       px%  px%  -     -      -        disabled -       caption   -      -       action  -
Graphic Button      -       px%  px%  -     -      capt.pos disabled img.url caption   -      -       action  -
Icon                -       px%  px%  -     -      capt.pos border   img.url caption   img.f2 -       action  -
Checkbox            yes     px%  px%  -     -      checked  disabled value   label     -      init    action  valid.
Radio Button        yes     px%  px%  -     -      checked  disabled value   label     -      init    action  valid.
Select List         yes     px%  px%  px    px     multiple disabled size    options   -      init    action  valid.
Query List          yes     px%  px%  px%   m.rows dsp.hdr  dsp.ckbx setting query     cols   -       -       -
Text                yes     px%  px%  szpx  maxlen password dis/rdo  value   -         -      init    action  valid.
Textarea            yes     px%  px%  szpx  colpx  -        dis/rdo  value   -         -      init    action  valid.
File Upload         yes     px%  px%  size  limit  -        disabled dir     types     -      init    action  valid.
Hidden Input        yes     -    -     -     -     -        -        value   -         -      init    -       valid.
---------------------------------------------------------------------------------------------------------------------

Query List Settings: border / cellspacing / cellpadding / <tr(h)>class / <tr(1)>class / <tr(2)>class
*/
	public $class1 = null;     // css class for <div>
	public $class2 = null;     // css class for <element>

	public $logging = null;     // element is logged in email/database no/yes

	public $posx = null;     // horizontal position in px or %
	public $posxmode = null;     // 0-px 1-%
	public $posy = null;     // vertical position in px or %
	public $posymode = null;     // 0-px 1-%
	public $width = null;     // width in % or px
	public $widthmode = null;     // 0-px 1-%
	public $height = null;     // height in px
	public $heightmode = null;     // 0-fixed px 1-auto 2-automax

	public $flag1 = null;     // element specific data, see xref
	public $flag2 = null;
	public $data1 = null;
	public $data2 = null;
	public $data3 = null;

	public $script1cond = null;     // init script
	public $script1flag1 = null;     // condition 1 = on form entry no/yes
	public $script1flag2 = null;     // condition 2 = on page entry
	public $script1id = null;     // script id
	public $script1code = null;     // custom code

	public $script2cond = null;     // action script
	public $script2flag1 = null;     // action 1 = Click
	public $script2flag2 = null;     // action 2 = Blur
	public $script2flag3 = null;     // action 3 = Change
	public $script2flag4 = null;     // action 4 = Focus
	public $script2flag5 = null;     // action 5 = Select
	public $script2id = null;     // script id
	public $script2code = null;     // custom code

	public $script3cond = null;     // validation script
	public $script3id = null;     // script id
	public $script3msg = null;     // message
	public $script3code = null;     // custom code

	public $mailback = null;
	public $mailbackfile = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_elements', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = null)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_elements where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val)
				if ($prop[0] != '_')
					@$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsElements

class facileFormsScripts extends Table
{
	public $id = null;     		// identifier
	public $published = null;   // is published
	public $package = null;     // package name
	public $name = null;     	// function name
	public $title = null;     	// fancy name
	public $description = null; // description
	public $type = null;     	// type name
	public $code = null;     	// the code
	public $unit_tests = null;   // unit test definitions
	public $created = null;
	public $created_by = null;
	public $modified = null;
	public $modified_by = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_scripts', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_scripts where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val) {
				if ($prop[0] != '_') {
					@$this->$prop = $row->$prop;
				}
			}
			// Deprecated in PHP 7.2 version so code above is used

			// while (list($prop, $val) = each($arr))
			// 	if ($prop[0] != '_')
			// 		$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsScripts

class facileFormsPieces extends Table
{
	public $id = null;     			// identifier
	public $published = null;   	// is published
	public $package = null;     	// package name
	public $name = null;     		// function name
	public $title = null;     		// fancy name
	public $description = null; 	// description
	public $type = null;     		// type name
	public $code = null;     		// the code
	public $unit_tests = null;   // unit test definitions
	public $created = null;
	public $created_by = null;
	public $modified = null;
	public $modified_by = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_pieces', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_pieces where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val) {
				if ($prop[0] != '_') {
					@$this->$prop = $row->$prop;
				}
			}
			// Deprecated in PHP 7.2 version so code above is used

			// while (list($prop, $val) = each($arr))
			// 	if ($prop[0] != '_')
			// 		$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsPieces

class facileFormsRecords extends Table
{
	public $id = null;     		// identifier
	public $submitted = null;   // date and time
	public $form = null;     	// form id
	public $title = null;     	// form title
	public $name = null;     	// form name
	public $ip = null;     		// submitters ip
	public $browser = null;     // browser
	public $opsys = null;     	// operating system
	public $provider = null;    // provider
	public $viewed = null;     	// view status
	public $exported = null;    // export status
	public $archived = null;    // archive status
	public $paypal_tx_id = null;
	public $paypal_payment_date = null;
	public $paypal_testaccount = null;
	public $paypal_download_tries = null;

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_records', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_records where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val)
				if ($prop[0] != '_')
					$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsRecords

class facileFormsSubrecords extends Table
{
	public $id = null;     	// identifier
	public $record = null;  // record id
	public $element = null; // element id
	public $name = null;    // element name
	public $type = null;    // data type
	public $value = null;   // data value

	function __construct(&$db)
	{
		parent::__construct('#__facileforms_subrecords', 'id', Factory::getContainer()->get(DatabaseInterface::class));
	} // constructor

	public function load($id = null, $reset = true)
	{
		global $database;

		$database->setQuery("select * from #__facileforms_subrecords where id = $id");
		$rows = $database->loadObjectList();
		if ($rows) {
			$row = $rows[0];
			$arr = get_object_vars($this);
			foreach ($arr as $prop => $val)
				if ($prop[0] != '_')
					$this->$prop = $row->$prop;
			return true;
		} // if
		return false;
	} // load

} // class facileFormsSubrecords

class facileFormsQuerycols
{
	public $title = null;    // column title
	public $name = null;     // column name
	public $class1 = null;   // class for th
	public $class2 = null;   // class for td(1)
	public $class3 = null;   // class for td(2)
	public $thspan = null;   // th span
	public $align = null;    // 0-left 1-center 2-right
	public $valign = null;   // 0-top 1-middle 2-bottom 3-baseline
	public $wrap = null;     // 0-nowrap 1-wrap
	public $value = null;    // value field (php allowed)
	public $comp = null;     // complied value: array of array(type, value/code)

	public $width = null;
	public $widthmd = null;
	public $thalign = null;
	public $thvalign = null;
	public $thwrap = null;

	function __construct()
	{
		$this->title = '';
		$this->name = '';
		$this->class1 = '';
		$this->class2 = '';
		$this->class3 = '';
		$this->width = '';
		$this->widthmd = 0;
		$this->thspan = 1;
		$this->thalign = 0;
		$this->thvalign = 0;
		$this->thwrap = 0;
		$this->align = 0;
		$this->valign = 0;
		$this->wrap = 0;
		$this->value = '';
	} // constructor

	function unpack($line)
	{
		$vals = explode('&', $line);
		$cnt = count($vals);
		if ($cnt > 0)
			$this->title = impstring($vals[0]);
		if ($cnt > 1)
			$this->name = impstring($vals[1]);
		if ($cnt > 2)
			$this->class1 = impstring($vals[2]);
		if ($cnt > 3)
			$this->class2 = impstring($vals[3]);
		if ($cnt > 4)
			$this->class3 = impstring($vals[4]);
		if ($cnt > 5)
			$this->width = impstring($vals[5]);
		if ($cnt > 6)
			$this->widthmd = impstring($vals[6]);
		if ($cnt > 7)
			$this->thspan = impstring($vals[7]);
		if ($cnt > 8)
			$this->thalign = impstring($vals[8]);
		if ($cnt > 9)
			$this->thvalign = impstring($vals[9]);
		if ($cnt > 10)
			$this->thwrap = impstring($vals[10]);
		if ($cnt > 11)
			$this->align = impstring($vals[11]);
		if ($cnt > 12)
			$this->valign = impstring($vals[12]);
		if ($cnt > 13)
			$this->wrap = impstring($vals[13]);
		if ($cnt > 14)
			$this->value = impstring($vals[14]);
	} // unpack

	function pack()
	{
		return
			expstring($this->title) . '&' .
			expstring($this->name) . '&' .
			expstring($this->class1) . '&' .
			expstring($this->class2) . '&' .
			expstring($this->class3) . '&' .
			expstring($this->width) . '&' .
			expstring($this->widthmd) . '&' .
			expstring($this->thspan) . '&' .
			expstring($this->thalign) . '&' .
			expstring($this->thvalign) . '&' .
			expstring($this->thwrap) . '&' .
			expstring($this->align) . '&' .
			expstring($this->valign) . '&' .
			expstring($this->wrap) . '&' .
			expstring($this->value);
	} // pack

} // class facileFormsQuerycols

?>
