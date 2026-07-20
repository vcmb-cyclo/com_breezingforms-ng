<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Pieces;

\defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Vcmb\Component\BreezingformsNG\Administrator\Model\PiecesModel;
use Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $option = 'com_breezingformsng';

    public string $package = '';

    public string $search = '';

    public int $total = 0;

    public int $limit = 10;

    public int $limitStart = 0;

    public array $packageList = [];

    public array $rows = [];

    public $pagination = null;

    public string $listOrder = 'a.name';

    public string $listDirn = 'asc';

    public string $filterState = '';

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PiecesModel) {
            throw new \RuntimeException('Unable to create BreezingForms NG pieces model.');
        }

        $app = Factory::getApplication();
        $input = $app->getInput();

        if ($this->package === '') {
            $this->package = $input->getString('pkg', '');
        }

        $list = $model->prepareList($this->package, $input, $app->getSession());

        $this->package = $list['package'];
        $this->packageList = $list['packageList'];
        $this->search = $list['search'];
        $this->total = $list['total'];
        $this->limit = $list['limit'];
        $this->limitStart = $list['limitStart'];
        $this->rows = $list['rows'];
        $this->pagination = $list['pagination'];
        $this->listOrder = $list['listOrder'];
        $this->listDirn = $list['listDirn'];
        $this->filterState = $list['filterState'];

        ToolbarHelper::help(
            'COM_BREEZINGFORMSNG_HELP_PIECES_TITLE',
            false,
            Uri::base() . 'index.php?option=com_breezingformsng&view=pieces&layout=help&tmpl=component'
        );

        parent::display($tpl);
    }
}
