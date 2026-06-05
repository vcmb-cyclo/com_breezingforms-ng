<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2004-2005 by Peter Koch
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Vcmb\Component\BreezingformsNG\Administrator\Model\PieceModel;
use Vcmb\Component\BreezingformsNG\Administrator\View\Pieces\HtmlView as PiecesView;

require_once ($ff_admpath . '/admin/piece.class.php');

$pkg = getPiecePackage();
switch ($task) {
	case 'save':
		facileFormsPiece::save($option, $pkg);
		break;
	case 'cancel':
		facileFormsPiece::cancel($option, $pkg);
		break;
	case 'edit':
		facileFormsPiece::edit($option, $pkg, $ids);
		break;
	case 'new':
		$ids = array();
		facileFormsPiece::edit($option, $pkg, $ids);
		break;
	case 'copy':
		facileFormsPiece::copy($option, $pkg, $ids);
		break;
	case 'remove':
		facileFormsPiece::del($option, $pkg, $ids);
		break;
	case 'publish':
		facileFormsPiece::publish($option, $pkg, $ids, '1');
		break;
	case 'unpublish':
		facileFormsPiece::publish($option, $pkg, $ids, '0');
		break;
	case 'prev':
		facileFormsPiece::prev($option, $pkg, $ids);
		break;
	case 'next':
		facileFormsPiece::next($option, $pkg, $ids);
		break;
	case 'test':
		facileFormsPiece::test($option, $pkg, $ids);
		break;
	case 'testrun':
		facileFormsPiece::testrun($option, $pkg, $ids);
		break;
	case 'testrunajax':
		facileFormsPiece::testrunajax($option, $pkg, $ids);
		break;
	case 'config':
		$ff_config->edit(
			$option,
			"index.php?option=$option&act=managepieces",
			$pkg
		);
		break;
	default:
		$factory = Factory::getApplication()->bootComponent($option)->getMVCFactory();
		$model = createPieceModel();
		$view = $factory->createView('Pieces', 'Administrator', 'Html');

		if (!$model instanceof PieceModel || !$view instanceof PiecesView) {
			throw new RuntimeException('Unable to create BreezingForms NG pieces MVC objects.');
		}

		$view->setModel($model, true);
		$view->option = $option;
		$view->package = $pkg;
		$view->display();
		break;
} // switch

function getPiecePackage()
{
	global $ff_config;
	$pkg = BFRequest::getVar('pkg', null);
	if (is_null($pkg))
		$pkg = $pkg = $ff_config->piecepkg;
	else
		if ($pkg === '')
			$pkg = '';
		else if ($pkg == '- blank -')
			$pkg = '';
		else {
			if (!createPieceModel()->packageExists($pkg))
				$pkg = $ff_config->piecepkg;
		} // if
	if ($pkg != $ff_config->piecepkg) {
		$ff_config->piecepkg = $pkg;
		$ff_config->store();
	} // if
	return $pkg;
} // getPiecePackage

function createPieceModel(): PieceModel
{
	$model = Factory::getApplication()
		->bootComponent('com_breezingformsng')
		->getMVCFactory()
		->createModel('Piece', 'Administrator', ['ignore_request' => true]);

	if (!$model instanceof PieceModel) {
		throw new RuntimeException('Unable to create BreezingForms NG piece model.');
	}

	return $model;
}

?>
