<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Source history: admin/integrator.html.php (git mv — Phase 3).
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\View\Integrator;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Vcmb\Component\BreezingformsNG\Administrator\Model\IntegratorModel;

class HtmlView extends \Vcmb\Component\BreezingformsNG\Administrator\View\BreezingformsNG\HtmlView
{
    public array $rules        = [];
    public ?\stdClass $rule    = null;
    public array $items        = [];
    public array $tableNames   = [];
    public array $tableColumns = [];
    public array $forms        = [];
    public array $formElements = [];
    public array $criteria         = [];
    public array $criteriaJoomla   = [];
    public array $criteriaFixed    = [];
    public string $formFilter  = 'all';

    public function display($tpl = null): void
    {
        $input  = Factory::getApplication()->getInput();
        $layout = $input->getCmd('layout', 'default');
        $model  = $this->getIntegratorModel();

        if ($layout === 'edit') {

            $input->set('hidemainmenu', 1);

        }


        if ($layout === 'edit') {
            $this->prepareEdit($model, $input);
        } else {
            $this->prepareList($model);
        }

        parent::display($tpl);
    }

    private function prepareList(IntegratorModel $model): void
    {
        $this->rules = $model->getRules();
        ToolbarHelper::addNew('integrator.edit');
        ToolbarHelper::deleteList('', 'integrator.remove');

        $document = Factory::getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->registerAndUseScript(
            'com_breezingformsng.admin-form',
            'media/com_breezingformsng/js/admin/admin-form.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['core']
        );
        $document->addScriptOptions('com_breezingformsng.admin-form', ['confirmDeleteTask' => 'integrator.remove']);
        Text::script('JGLOBAL_CONFIRM_DELETE');

        $wa->registerAndUseScript(
            'com_breezingformsng.admin-toggle-published',
            'media/com_breezingformsng/js/admin/admin-toggle-published.js',
            ['version' => 'auto'],
            ['defer' => true],
            ['core']
        );
        $document->addScriptOptions('com_breezingformsng.admin-toggle-published', ['csrfToken' => Session::getFormToken()]);
        Text::script('JPUBLISHED');
        Text::script('JUNPUBLISHED');
    }

    private function prepareEdit(IntegratorModel $model, \Joomla\Input\Input $input): void
    {
        $id         = $input->getInt('id', 0);
        $this->rule = $id > 0 ? $model->getRule($id) : null;

        $this->formFilter  = $input->getCmd('formfilter', 'all');
        $this->forms       = $model->getForms($this->formFilter);
        $this->tableNames  = $model->getTableNames();

        if ($this->rule !== null) {
            $this->items          = $model->getItems($id);
            $this->tableColumns   = $model->getTableColumns($this->rule->reference_table);
            $this->formElements   = $model->getFormElements((int) $this->rule->form_id);
            $this->criteria       = $model->getCriteria($id);
            $this->criteriaJoomla = $model->getCriteriaJoomla($id);
            $this->criteriaFixed  = $model->getCriteriaFixed($id);
        }

        if ($id === 0) {
            ToolbarHelper::save('integrator.save');
        }
        ToolbarHelper::cancel('integrator.display', 'JTOOLBAR_CLOSE');
    }

    protected function getDetailLabel(): ?string
    {
        if ($this->rule === null) {
            return null;
        }

        $name = trim((string) $this->rule->name);

        return $name !== '' ? $name : Text::_('COM_BREEZINGFORMSNG_INSTALLER_UNKNOWN');
    }

    private function getIntegratorModel(): IntegratorModel
    {
        $model = Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Integrator', 'Administrator');

        if (!$model instanceof IntegratorModel) {
            throw new \RuntimeException('IntegratorModel not found');
        }

        return $model;
    }
}
