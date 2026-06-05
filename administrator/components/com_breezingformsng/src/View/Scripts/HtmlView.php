<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Scripts;

\defined('_JEXEC') or die;

use Vcmb\Component\BreezingformsNG\Administrator\Model\ScriptsModel;
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

    public $pagination = null;

    public function display($tpl = null)
    {
        $model = \Joomla\CMS\Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Scripts', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof ScriptsModel) {
            throw new \RuntimeException('Unable to create BreezingForms NG scripts model.');
        }

        $this->setModel($model, true);

        if ($this->package === '') {
            $this->package = \Joomla\CMS\Factory::getApplication()->getInput()->getString('pkg', '');
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
        $this->pagination = $list['pagination'];

        parent::display($tpl);
    }
}
