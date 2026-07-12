<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright (C) 2024 - 2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

namespace Vcmb\Component\BreezingformsNG\Administrator\Service;

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use facileFormsScripts;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use RuntimeException;
use Vcmb\Component\BreezingformsNG\Administrator\Model\ScriptModel;
use Vcmb\Component\BreezingformsNG\Administrator\View\Scripts\Renderer;
use Joomla\CMS\Language\Text;

class ScriptManager
{
	static function edit($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		ArrayHelper::toInteger($ids);
		$typelist = array();
		$typelist[] = array('Untyped', Text::_('COM_BREEZINGFORMSNG_SCRIPTS_UNTYPED'));
		$typelist[] = array('Element Init', Text::_('COM_BREEZINGFORMSNG_SCRIPTS_ELEMENTINIT'));
		$typelist[] = array('Element Action', Text::_('COM_BREEZINGFORMSNG_SCRIPTS_ELEMENTACTION'));
		$typelist[] = array('Element Validation', Text::_('COM_BREEZINGFORMSNG_SCRIPTS_ELEMENTVALID'));
		$typelist[] = array('Form Init', Text::_('COM_BREEZINGFORMSNG_SCRIPTS_FORMINIT'));
		$typelist[] = array('Form Submitted', Text::_('COM_BREEZINGFORMSNG_SCRIPTS_FORMSUBMIT'));
		$row = new facileFormsScripts($database);
		if (count($ids)) {
			$row->load($ids[0]);
		} else {
			$row->type = $typelist[0];
			$row->package = $pkg;
			$row->published = 1;
		} // if
		Renderer::edit($option, $pkg, $row, $typelist);
	} // edit


	// ✅ FORCER le champ code en RAW (conserve < et >)
	static function save($option, $pkg)
	{
		$app = Factory::getApplication();

		// Lire le body brut
		$rawBody = file_get_contents('php://input');

		// Parser comme application/x-www-form-urlencoded
		$post = [];
		parse_str($rawBody, $post);

		// Récupérer code tel qu'envoyé
		$code = $post['code'] ?? '';
		$unitTests = $post['unit_tests'] ?? '';

		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$row      = new facileFormsScripts($database);

		// bind du reste
		if (!$row->bind($_POST)) {
			Factory::getApplication()->enqueueMessage($row->getError(), 'error');
			Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
		}

		// Forcer code non filtré
		$row->code = $code;
		$row->unit_tests = $unitTests;

		$now = (new \Joomla\CMS\Date\Date())->toSql();
		$userId = (string) Factory::getApplication()->getIdentity()->username;

		if (empty($row->id)) {
			if (empty($row->created)) {
				$row->created = $now;
			}
			if (empty($row->created_by)) {
				$row->created_by = $userId;
			}
		}

		$row->modified = $now;
		$row->modified_by = $userId;

		if (!$row->store()) {
			Factory::getApplication()->enqueueMessage($row->getError(), 'error');
			Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
		}

		$app->enqueueMessage(Text::_('COM_BREEZINGFORMSNG_SCRIPTS_SAVED'));
		$app->redirect("index.php?option=$option&task=scripts.edit&pkg=$pkg&ids[]=" . (int) $row->id);
	}


	static function cancel($option, $pkg)
	{
		Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
	} // cancel

	static function copy($option, $pkg, $ids)
	{
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		$total = count($ids);
		$row = new facileFormsScripts($database);
		if (count($ids)) foreach ($ids as $id) {
			$row->load(intval($id));
			$row->id       = NULL;
			$row->created = (new \Joomla\CMS\Date\Date())->toSql();
			$row->created_by = (string) Factory::getApplication()->getIdentity()->username;
			$row->modified = $row->created;
			$row->modified_by = $row->created_by;
			$row->store();
		} // foreach
		$msg = $total . ' ' . Text::_('COM_BREEZINGFORMSNG_SCRIPTS_SUCCOPIED');
		Factory::getApplication()->enqueueMessage($msg);
		Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
	} // copy

	static function del($option, $pkg, $ids)
	{
		$model = ScriptModel::create();

		try {
			$total = $model->deleteByIds($ids);
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
		}

		if ($total) {
			Factory::getApplication()->enqueueMessage(
				$total . ' ' . Text::_('COM_BREEZINGFORMSNG_SCRIPTS_SUCCDELETED'),
				'message'
			);
		}
		Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
	} // del

	static function publish($option, $pkg, $ids, $publish)
	{
		try {
			ScriptModel::create()->publishByIds($ids, (bool) $publish);
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
		}

		Factory::getApplication()->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
	} // publish

	static function listitems($option, $pkg)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$session = $app->getSession();

		try {
			$pkgs = ScriptModel::create()->getPackages();
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$pkgok = $pkg == '';
		if (!$pkgok && count($pkgs)) foreach ($pkgs as $p) if ($p->name == $pkg) {
			$pkgok = true;
			break;
		}
		if (!$pkgok) $pkg = '';
		$pkglist = array();
		$pkglist[] = array($pkg == '', '');
		if (count($pkgs)) foreach ($pkgs as $p) $pkglist[] = array($p->name == $pkg, $p->name);
		$searchReq = $input->get('search', null, 'string');
		if ($searchReq === null) {
			$search = (string) $session->get('bf.scripts_search', '');
		} else {
			$search = trim((string) $searchReq);
			$session->set('bf.scripts_search', $search);
		}

		$sortReq = $input->get('sort', null, 'string');
		$dirReq = $input->get('dir', null, 'cmd');

		if ($sortReq === null) {
			$sort = (string) $session->get('bf.scripts_sort', 'name');
		} else {
			$sort = (string) $sortReq;
			$session->set('bf.scripts_sort', $sort);
		}

		if ($dirReq === null) {
			$dir = strtoupper((string) $session->get('bf.scripts_dir', 'ASC'));
		} else {
			$dir = strtoupper((string) $dirReq);
			$session->set('bf.scripts_dir', $dir);
		}

		$dir = $dir === 'DESC' ? 'DESC' : 'ASC';
		$pageSizes = array(10, 25, 50, 100, 250, 500, 1000, 5000, 10000, 100000);
		$limitReq = $input->getInt('limit', -1);

		if ($limitReq > 0 && in_array($limitReq, $pageSizes, true)) {
			$limit = $limitReq;
			$session->set('bf.scripts_limit', $limit);
		} else {
			$limit = (int) $session->get('bf.scripts_limit', 10);

			if (!in_array($limit, $pageSizes, true)) {
				$limit = 10;
			}
		}

		$limitstartReq = $input->getInt('limitstart', -1);

		if ($limitstartReq >= 0) {
			$limitstart = $limitstartReq;
		} else {
			$limitstart = (int) $session->get('bf.scripts_limitstart', 0);
		}

		if ($limitstart < 0) {
			$limitstart = 0;
		}

		try {
			$listData = ScriptModel::create()->getListData($pkg, $search, $sort, $dir, $limit, $limitstart);
		} catch (\Exception $e) {
			echo $e->getMessage();
			return false;
		} // try

		$total = $listData['total'];
		$limitstart = $listData['limitstart'];
		$session->set('bf.scripts_limitstart', $limitstart);
		$rows = $listData['rows'];

		Renderer::listitems($option, $rows, $pkglist, $pkg, $search, $total, $limit, $limitstart, $pageSizes);
	} // listitems

	static function test($option, $pkg, $ids)
	{
		$app = Factory::getApplication();
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		ArrayHelper::toInteger($ids);
		if (!count($ids)) {
			$id = $app->getInput()->getInt('id', 0);
			if ($id) {
				$ids = array($id);
			}
		}
		if (!count($ids)) {
			$app->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
			return;
		}

		$row = new facileFormsScripts($database);
		$row->load($ids[0]);
		if (!(int) $row->id) {
			$app->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
			return;
		}

		list($functionName, $params, $paramDefaults) = self::extractFunctionSignature((string) $row->code, (string) $row->name);
		$autoRun = false;
		if (count($params) === 0) {
			$autoRun = true;
		} else {
			$allDefaults = true;
			for ($i = 0; $i < count($params); $i++) {
				$default = isset($paramDefaults[$i]) ? trim((string) $paramDefaults[$i]) : '';
				if ($default === '') {
					$allDefaults = false;
					break;
				}
			}
			$autoRun = $allDefaults;
		}
		$testMode = $app->getInput()->getCmd('test_mode', '');
		Renderer::test($option, $pkg, $row, $functionName, $params, $paramDefaults, $autoRun, $testMode);
	}

	static function prev($option, $pkg, $ids)
	{
		self::navigate($option, $pkg, $ids, 'prev');
	}

	static function next($option, $pkg, $ids)
	{
		self::navigate($option, $pkg, $ids, 'next');
	}

	private static function navigate($option, $pkg, $ids, $direction)
	{
		$app = Factory::getApplication();
		$database = Factory::getContainer()->get(DatabaseInterface::class);
		ArrayHelper::toInteger($ids);
		if (!count($ids)) {
			$id = $app->getInput()->getInt('id', 0);
			if ($id) {
				$ids = array($id);
			}
		}
		if (!count($ids)) {
			$app->redirect("index.php?option=$option&view=scripts&pkg=$pkg");
			return;
		}

		$currentId = (int) $ids[0];
		$pkgCondition = $pkg !== '' ? "package = " . $database->Quote($pkg) : "1=1";
		if ($direction === 'prev') {
			$database->setQuery(
				"SELECT id FROM #__facileforms_scripts WHERE " . $pkgCondition .
				" AND id < " . $currentId . " ORDER BY id DESC LIMIT 1"
			);
		} else {
			$database->setQuery(
				"SELECT id FROM #__facileforms_scripts WHERE " . $pkgCondition .
				" AND id > " . $currentId . " ORDER BY id ASC LIMIT 1"
			);
		}
		$targetId = (int) $database->loadResult();
		if (!$targetId) {
			if ($direction === 'prev') {
				$database->setQuery(
					"SELECT id FROM #__facileforms_scripts WHERE " . $pkgCondition .
					" ORDER BY id DESC LIMIT 1"
				);
			} else {
				$database->setQuery(
					"SELECT id FROM #__facileforms_scripts WHERE " . $pkgCondition .
					" ORDER BY id ASC LIMIT 1"
				);
			}
			$targetId = (int) $database->loadResult();
			if (!$targetId) {
				$targetId = $currentId;
			}
		}

		$testContext = $app->getInput()->getInt('test_context', 0);
		$testMode = $app->getInput()->getCmd('test_mode', '');
		if ($testContext) {
			$testModeQuery = $testMode !== '' ? '&test_mode=' . urlencode($testMode) : '';
			$app->redirect("index.php?option=$option&task=scripts.test&pkg=$pkg&ids[]=" . $targetId . $testModeQuery);
		} else {
			$app->redirect("index.php?option=$option&task=scripts.edit&pkg=$pkg&ids[]=" . $targetId);
		}
	}

	private static function extractFunctionSignature($code, $fallbackName = '')
	{
		$functionName = '';
		$params = array();
		$defaults = array();

		$patterns = array(
			'/function\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*\(([^)]*)\)/m',
			'/(?:const|let|var)?\s*([a-zA-Z_$][a-zA-Z0-9_$]*)\s*=\s*function\s*\(([^)]*)\)/m',
			'/(?:const|let|var)?\s*([a-zA-Z_$][a-zA-Z0-9_$]*)\s*=\s*\(([^)]*)\)\s*=>/m',
		);

		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $code, $matches)) {
				$functionName = $matches[1];
				$paramList = trim($matches[2]);
				if ($paramList !== '') {
					$parts = explode(',', $paramList);
					foreach ($parts as $part) {
						$part = trim($part);
						if ($part === '') {
							continue;
						}
						$part = preg_replace('/^\.{3}/', '', $part);
						$segments = explode('=', $part, 2);
						$name = trim($segments[0]);
						if (!preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', $name)) {
							continue;
						}
						$params[] = $name;
						$defaults[] = isset($segments[1]) ? trim($segments[1]) : '';
					}
				}
				break;
			}
		}

		if ($functionName === '') {
			$functionName = trim((string) $fallbackName);
		}

		return array($functionName, $params, $defaults);
	}

}
