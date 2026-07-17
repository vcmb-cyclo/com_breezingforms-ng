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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseInterface;

// Legacy configuration object, backed by the component parameters.
class facileFormsConf
{
	public $stylesheet = 1;        	// backend frame preview no/yes
	public $wysiwyg = 0;        	// use wysiwyg editor for static text
	public $areasmall = 4;        	// small textarea lines
	public $areamedium = 12;       	// medium textarea lines
	public $arealarge = 20;       	// large textarea lines
	public $limitdesc = 100;      	// listview description limit
	public $emailadr = 'Enter your email address here';                  // default email notify address
	public $images = '{mossite}/media/com_breezingformsng/images/site';    // {ff_images} path
	public $uploads = '{mospath}/media/breezingforms/uploads';   // {ff_uploads} path
	public $movepixels = 10;       	// pixelmover stepping
	public $compress = 1;        	// compress output
	public $livesite = 0;        	// use $mosConfig_live_site as site url
	public $getprovider = 0;        // get provider with gethostbyaddr
	public $gridshow = 1;        	// show grid in preview
	public $gridsize = 10;       	// grid size
	public $gridcolor1 = '#e0e0ff';	// grid color even lines
	public $gridcolor2 = '#ffe0e0';	// grid color odd lines

	// record manager settings
	public $viewed = 0;        		// default viewed filter setting
	public $exported = 0;        	// default exported filter setting
	public $archived = 0;        	// default archived filter setting
	public $formname = '';       	// default formname filter setting

	public $menupkg = '';       	// last selected menu package
	public $formpkg = '';       	// last selected form package
	public $scriptpkg = '';       	// last selected script package
	public $piecepkg = '';       	// last selected piece package

	public $csvdelimiter = ";";
	public $csvquote = '"';
	public $cellnewline = 1;

		public $disable_ip = 0;

	function __construct()
	{
		$this->load();
	} // constructor

	function load()
	{
		global $database;

		$database = Factory::getContainer()->get(DatabaseInterface::class);

		$params = ComponentHelper::getParams('com_breezingformsng');

		foreach (['disable_ip', 'emailadr', 'uploads', 'csvdelimiter', 'csvquote', 'cellnewline'] as $key) {
			$value = $params->get($key);
			if ($value !== null) {
				$this->$key = $value;
			}
		}
	} // load
} // class facileFormsConf

