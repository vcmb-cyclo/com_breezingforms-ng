<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Vcmb\Component\BreezingformsNG\Administrator\Model\IntegratorModel;

class IntegratorController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $input = Factory::getApplication()->getInput();
        $input->set('view', 'integrator');
        $input->set('layout', 'default');
        return parent::display($cachable, $urlparams);
    }

    public function edit(): void
    {
        $input = Factory::getApplication()->getInput();
        $input->set('view', 'integrator');
        $input->set('layout', 'edit');
        parent::display();
    }

    public function save(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $model  = $this->getIntegratorModel();
        $name   = $input->getString('rule_name', '');
        $formId = $input->getInt('form_id', 0);
        $table  = $input->getString('reference_table', '');
        $type   = $input->getString('type', 'insert');

        $id = $model->saveRule($name, $formId, $table, $type);
        $this->setRedirect($this->editUrl($id));
    }

    public function remove(): void
    {
        $this->checkToken();
        $ids = (array) Factory::getApplication()->getInput()->get('cid', [], 'ARRAY');
        $this->getIntegratorModel()->deleteRules($ids);
        $this->setRedirect($this->listUrl());
    }

    public function publish(): void
    {
        $this->checkToken();
        $id = Factory::getApplication()->getInput()->getInt('publish_id', 0);
        $this->getIntegratorModel()->publishRule($id, 1);
        $this->setRedirect($this->listUrl());
    }

    public function unpublish(): void
    {
        $this->checkToken();
        $id = Factory::getApplication()->getInput()->getInt('publish_id', 0);
        $this->getIntegratorModel()->publishRule($id, 0);
        $this->setRedirect($this->listUrl());
    }

    public function addItem(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $model  = $this->getIntegratorModel();
        $model->addItem(
            $ruleId,
            $input->getInt('element_id', 0),
            $input->getString('reference_column', '')
        );
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function removeItem(): void
    {
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->removeItem($input->getInt('itemId', 0));
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function saveCode(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->saveCode(
            $input->getInt('itemId', 0),
            $ruleId,
            $input->post->getRaw('code') ?? ''
        );
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function saveFinalizeCode(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->saveFinalizeCode(
            $ruleId,
            $input->post->getRaw('finalizeCode') ?? ''
        );
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function addCriteria(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->addCriteria(
            $ruleId,
            $input->getString('operator', '='),
            $input->getString('reference_column', ''),
            $input->getInt('element_id', 0),
            $input->getString('andor', 'AND')
        );
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function removeCriteria(): void
    {
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->removeCriteria($input->getInt('criteriaId', 0));
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function addCriteriaJoomla(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->addCriteriaJoomla(
            $ruleId,
            $input->getString('operator', '='),
            $input->getString('reference_column', ''),
            $input->getString('joomla_object', 'Userid'),
            $input->getString('andor', 'AND')
        );
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function removeCriteriaJoomla(): void
    {
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->removeCriteriaJoomla($input->getInt('criteriaId', 0));
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function addCriteriaFixed(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->addCriteriaFixed(
            $ruleId,
            $input->getString('operator', '='),
            $input->getString('reference_column', ''),
            $input->getString('fixed_value', ''),
            $input->getString('andor', 'AND')
        );
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function removeCriteriaFixed(): void
    {
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->removeCriteriaFixed($input->getInt('criteriaId', 0));
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function publishItem(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->publishItem($input->getInt('publish_id', 0), 1);
        $this->setRedirect($this->editUrl($ruleId));
    }

    public function unpublishItem(): void
    {
        $this->checkToken();
        $input  = Factory::getApplication()->getInput();
        $ruleId = $input->getInt('id', 0);
        $this->getIntegratorModel()->publishItem($input->getInt('publish_id', 0), 0);
        $this->setRedirect($this->editUrl($ruleId));
    }

    private function editUrl(int $id): string
    {
        return 'index.php?option=com_breezingformsng&view=integrator&layout=edit&id=' . $id;
    }

    private function listUrl(): string
    {
        return 'index.php?option=com_breezingformsng&view=integrator';
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
