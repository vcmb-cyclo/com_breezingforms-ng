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

final class QueryColumn
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

} // class QueryColumn

