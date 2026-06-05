<?php
/**
 * @package     BreezingForms NG
 * @copyright   Copyright (C) 2024-2026 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Scripts;

\defined('_JEXEC') or die;

use Vcmb\Component\BreezingformsNG\Administrator\Model\ScriptModel;
use Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $option = 'com_breezingformsng';

    public string $package = '';

    public string $search = '';

    public int $total = 0;

    public int $limit = 10;

    public int $limitStart = 0;

    public array $pageSizes = [];

    public array $packageList = [];

    public array $rows = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof ScriptModel) {
            $model = ScriptModel::create();
            $this->setModel($model, true);
        }

        $list = $model->prepareList($this->package);

        $this->package = $list['package'];
        $this->packageList = $list['packageList'];
        $this->search = $list['search'];
        $this->total = $list['total'];
        $this->limit = $list['limit'];
        $this->limitStart = $list['limitStart'];
        $this->pageSizes = $list['pageSizes'];
        $this->rows = $list['rows'];

        $this->addTemplatePath(__DIR__ . '/tmpl');

        parent::display($tpl);
    }
}
