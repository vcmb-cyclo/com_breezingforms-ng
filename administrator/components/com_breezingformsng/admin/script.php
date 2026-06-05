<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.4.4
* @package BreezingForms
* @copyright (C) 2004-2005 by Peter Koch
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Vcmb\Component\BreezingformsNG\Administrator\Model\ScriptModel;
use Vcmb\Component\BreezingformsNG\Administrator\View\Scripts\HtmlView as ScriptsView;

require_once($ff_admpath.'/admin/script.class.php');

$pkg = getScriptPackage();
switch ($task) {
	case 'save' :
		facileFormsScript::save($option, $pkg);
		break;
	case 'cancel':
		facileFormsScript::cancel($option, $pkg);
		break;
	case 'edit' :
		facileFormsScript::edit($option, $pkg, $ids);
		break;
	case 'new' :
		$ids = array();
		facileFormsScript::edit($option, $pkg, $ids);
		break;
	case 'copy' :
		facileFormsScript::copy($option, $pkg, $ids);
		break;
	case 'remove' :
		facileFormsScript::del($option, $pkg, $ids);
		break;
	case 'publish' :
		facileFormsScript::publish($option, $pkg, $ids, '1');
		break;
	case 'unpublish' :
		facileFormsScript::publish($option, $pkg, $ids, '0');
		break;
	case 'prev':
		facileFormsScript::prev($option, $pkg, $ids);
		break;
	case 'next':
		facileFormsScript::next($option, $pkg, $ids);
		break;
	case 'test':
		facileFormsScript::test($option, $pkg, $ids);
		break;
	case 'config' :
		$ff_config->edit(
			$option,
			"index.php?option=$option&act=managescripts",
			$pkg
		);
		break;
	default:
		$factory = Factory::getApplication()->bootComponent($option)->getMVCFactory();
		$model = createScriptModel();
		$view = $factory->createView('Scripts', 'Administrator', 'Html');

		if (!$model instanceof ScriptModel || !$view instanceof ScriptsView) {
			throw new RuntimeException('Unable to create BreezingForms NG scripts MVC objects.');
		}

		$view->setModel($model, true);
		$view->option = $option;
		$view->package = $pkg;
		$view->display();
		break;
} // switch

function getScriptPackage()
{
	global $ff_config;
	$pkg = BFRequest::getVar( 'pkg', null);
	if (is_null($pkg))
		$pkg = $pkg = $ff_config->scriptpkg;
	else
	if ($pkg === '')
		$pkg = '';
	else if ($pkg == '- blank -')
		$pkg = '';
	else {
		if (!createScriptModel()->packageExists($pkg)) $pkg = $ff_config->scriptpkg;
	} // if
	if ($pkg != $ff_config->scriptpkg) {
		$ff_config->scriptpkg = $pkg;
		$ff_config->store();
	} // if
	return $pkg;
} // getScriptPackage

function createScriptModel(): ScriptModel
{
	$model = Factory::getApplication()
		->bootComponent('com_breezingformsng')
		->getMVCFactory()
		->createModel('Script', 'Administrator', ['ignore_request' => true]);

	if (!$model instanceof ScriptModel) {
		throw new RuntimeException('Unable to create BreezingForms NG script model.');
	}

	return $model;
}

?>
