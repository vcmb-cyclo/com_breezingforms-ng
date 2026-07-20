<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Table;

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

final class ScriptTable extends Table
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

	public function __construct(DatabaseInterface $db)
	{
		parent::__construct('#__facileforms_scripts', 'id', $db);
	} // constructor

	public function load($id = null, $reset = true)
	{
		return parent::load($id, $reset ?? true);
	}

} // class ScriptTable

