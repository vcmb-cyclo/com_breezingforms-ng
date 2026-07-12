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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

// Legacy runtime helpers of the form engine.

function nl()
{
	return "\r\n";
} // nl

function nlc()
{
	global $ff_config;
	if (!$ff_config->compress)
		return "\r\n";
} // nlc

function adjustNewlines($text)
{
	$text = str_replace("\r\n", "\n", $text); // unix mode
	return str_replace("\n", nl(), $text); // ff mode
} // adjustNewlines

function indent($level)
{
	$ind = '';
	for ($i = 0; $i < $level; $i++)
		$ind .= "\t";
	return $ind;
} // indent

function indentc($level)
{
	global $ff_config;
	$ind = '';
	if (!$ff_config->compress)
		for ($i = 0; $i < $level; $i++)
			$ind .= "\t";
	return $ind;
} // indentc

function expstring($text)
{
	$o = '';
	$text = trim($text);
	$l = strlen($text);
	for ($i = 0; $i < $l; $i++) {
		$c = $text[$i];
		switch ($c) {
			case ';':
				$o .= '\\x3B';
				break;
			case ',':
				$o .= '\\x2C';
				break;
			case '&':
				$o .= '\\x26';
				break;
			case '<':
				$o .= '\\x3C';
				break;
			case '>':
				$o .= '\\x3E';
				break;
			case '\'':
				$o .= '\\x27';
				break;
			case '\\':
				$o .= '\\x5C';
				break;
			case '"':
				$o .= '\\x22';
				break;
			case "\n":
				$o .= '\\n';
				break;
			case "\r":
				$o .= '\\r';
				break;
			default:
				$o .= $c;
		} // switch
	} // for
	return $o;
} // expstring

function impstring($text)
{
	return stripcslashes($text);
} // impstring

function addRequestParams($params)
{
	global $ff_request;

	$is_quoted = false;

	$px = explode('&amp;', $params);
	if (count($px) == 0) {
		$px = explode('&', $params);
	}
	if (count($px))
		foreach ($px as $p) {
			$x = explode('=', $p);
			$c = count($x);
			$n = '';
			if ($c > 0)
				$n = trim($x[0]);
			$v = '';
			if ($c > 1)
				$v = trim($x[1]);
			if ($n != '')
				$ff_request[$n] = $v;
		} // foreach
} // addRequestParams

function ff_reserved($p, $ff_param = true)
{
	global $ff_resnames;

	$p = strtolower($p);
	if (substr($p, 0, 3) != 'ff_')
		return false;

	if ($ff_param && substr($p, 0, 9) == 'ff_param_')
		return true;

	if (count($ff_resnames))
		foreach ($ff_resnames as $n)
			if ($p == $n)
				return true;
	return false;
} // ff_reserved

function saveOtherParam($name)
{
	global $ff_otherparams;
	if (BFRequest::getVar($name, null) != null && !is_array(BFRequest::getVar($name, null))) {
		$value = BFRequest::getVar($name);
		$ff_otherparams[$name] = $value;
		return $value;
	} // if
	return NULL;
} // saveOtherParam

function initFacileForms()
{
	global $ff_mossite, $ff_comsite, $ff_config, $ff_otherparams, $mosConfig_live_site;
	$mainframe = Factory::getApplication();


	if (!isset($ff_mossite)) {
		if ($ff_config->livesite == 0) {
			//$ff_mossite = str_replace('\\','/', Uri::root());
			$ff_mossite = Uri::root();
		} else {
			$s = empty($_SERVER["HTTPS"]) ? '' : (($_SERVER["HTTPS"] == "on") ? "s" : "");
			$s = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? 's' : $s;

			$protocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
			$protocol = substr($protocol, 0, strpos($protocol, '/')) . $s;
			$port = ":" . $_SERVER["SERVER_PORT"];
			if (($protocol == 'http' && $port == ':80') || ($protocol == 'https' && $port == ':443'))
				$port = '';
			$path = dirname($_SERVER['PHP_SELF']);
			if (basename($path) == 'administrator')
				$path = dirname($path);
			$domain = $_SERVER['HTTP_HOST'];
			$p = strrpos($domain, ':');
			if ($p)
				$domain = substr($domain, 0, $p);
			$ff_mossite = str_replace('\\', '/', $protocol . "://" . $domain . $port . $path);
		} // if
		$len = strlen($ff_mossite);
		if ($len > 0 && $ff_mossite[$len - 1] == '/')
			$ff_mossite = substr($ff_mossite, 0, $len - 1);
	} // if

	if (!isset($ff_comsite))
		$ff_comsite = $ff_mossite . '/components/com_breezingformsng';

	if (!isset($ff_otherparams)) {
		$ff_otherparams = array();

		switch (saveOtherParam('option')) {
			case 'com_content':
				saveOtherParam('Itemid');
				saveOtherParam('task');
				saveOtherParam('sectionid');
				saveOtherParam('id');
				break;
			case 'com_contact':
			case 'com_contacts':
				saveOtherParam('id');
				saveOtherParam('Itemid');
				saveOtherParam('task');
				saveOtherParam('catid');
				saveOtherParam('view');
				saveOtherParam('contact_id');
				break;
			case 'com_weblinks':
				saveOtherParam('Itemid');
				saveOtherParam('catid');
				break;
			default:
				saveOtherParam('Itemid');
		} // switch
	} // if
} // initFacileForms

