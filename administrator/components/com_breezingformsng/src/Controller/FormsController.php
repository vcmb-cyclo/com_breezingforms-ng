<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Vcmb\Component\BreezingformsNG\Administrator\Model\FormModel;

class FormsController extends BaseController
{
    public function display($cachable = false, $urlparams = []): static
    {
        Factory::getApplication()->getInput()->set('view', 'forms');
        return parent::display($cachable, $urlparams);
    }

    public function edit(): void
    {
        $input = Factory::getApplication()->getInput();
        $id    = $input->getInt('id', 0);
        $pkg   = $input->getString('pkg', '');

        if ($id > 0) {
            Factory::getApplication()->redirect(Route::_(
                'index.php?option=com_breezingformsng&task=quickmode.display&form=' . $id . '&pkg=' . rawurlencode($pkg),
                false
            ));
            return;
        }

        Factory::getApplication()->redirect(Route::_(
            'index.php?option=com_breezingformsng&view=forms&layout=edit&id=' . $id . '&pkg=' . rawurlencode($pkg),
            false
        ));
    }

    public function save(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
            return;
        }

        // Read raw body to preserve PHP code fields from filter stripping
        $rawBody = (string) file_get_contents('php://input');
        $post    = [];
        parse_str($rawBody, $post);

        $data = $input->post->getArray([
            'id'                       => 'INT',
            'package'                  => 'STRING',
            'title'                    => 'STRING',
            'name'                     => 'STRING',
            'description'              => 'STRING',
            'class1'                   => 'STRING',
            'width'                    => 'INT',
            'widthmode'                => 'INT',
            'height'                   => 'INT',
            'heightmode'               => 'INT',
            'pages'                    => 'INT',
            'published'                => 'INT',
            'ordering'                 => 'INT',
            'runmode'                  => 'INT',
            'prevmode'                 => 'INT',
            'prevwidth'                => 'INT',
            'autoheight'               => 'INT',
            'emailntf'                 => 'INT',
            'emaillog'                 => 'INT',
            'emailxml'                 => 'INT',
            'dblog'                    => 'INT',
            'emailadr'                 => 'STRING',
            'custom_mail_subject'      => 'STRING',
            'alt_mailfrom'             => 'STRING',
            'alt_fromname'             => 'STRING',
            'email_type'               => 'INT',
            'email_custom_html'        => 'INT',
            'mb_emailntf'              => 'INT',
            'mb_emaillog'              => 'INT',
            'mb_emailxml'              => 'INT',
            'mb_emailadr'              => 'STRING',
            'mb_custom_mail_subject'   => 'STRING',
            'mb_alt_mailfrom'          => 'STRING',
            'mb_alt_fromname'          => 'STRING',
            'mb_email_type'            => 'INT',
            'mb_email_custom_html'     => 'INT',
            'script1cond'              => 'INT',
            'script1id'                => 'INT',
            'script2cond'              => 'INT',
            'script2id'                => 'INT',
            'piece1cond'               => 'INT',
            'piece1id'                 => 'INT',
            'piece2cond'               => 'INT',
            'piece2id'                 => 'INT',
            'piece3cond'               => 'INT',
            'piece3id'                 => 'INT',
            'piece4cond'               => 'INT',
            'piece4id'                 => 'INT',
        ]);

        // Override PHP code fields with raw (unfiltered) values
        foreach (['piece1code', 'piece2code', 'piece3code', 'piece4code',
                  'script1code', 'script2code', 'script3code',
                  'email_custom_template', 'mb_email_custom_template'] as $field) {
            $data[$field] = $post[$field] ?? '';
        }

        try {
            $id  = $this->getFormModel()->saveForm($data);
            $pkg = (string) ($data['package'] ?? '');
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_SAVE_SUCCESS'), 'message');
            $app->redirect(Route::_(
                'index.php?option=com_breezingformsng&task=quickmode.display&form=' . $id
                . ($pkg !== '' ? '&pkg=' . rawurlencode($pkg) : ''),
                false
            ));
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $id  = (int) ($data['id'] ?? 0);
            $pkg = (string) ($data['package'] ?? '');
            $app->redirect(Route::_(
                'index.php?option=com_breezingformsng&view=forms&layout=edit&id=' . $id . '&pkg=' . rawurlencode($pkg),
                false
            ));
        }
    }

    public function cancel(): void
    {
        $pkg = Factory::getApplication()->getInput()->getString('pkg', '');
        Factory::getApplication()->redirect(Route::_($this->listUrl($pkg), false));
    }

    public function remove(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            if (!empty($ids)) {
                $this->getFormModel()->deleteItems($ids);
                $app->enqueueMessage(Text::_('JLIB_APPLICATION_DELETE_SUCCESS'), 'message');
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    public function copy(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            if (!empty($ids)) {
                $this->getFormModel()->copyItems($ids);
                $app->enqueueMessage(
                    count($ids) . ' ' . Text::_('COM_BREEZINGFORMSNG_FORMS_SUCOPIED'),
                    'message'
                );
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    public function publish(): void
    {
        $this->togglePublish(1);
    }

    public function unpublish(): void
    {
        $this->togglePublish(0);
    }

    public function orderup(): void
    {
        $this->moveOrder(-1);
    }

    public function orderdown(): void
    {
        $this->moveOrder(1);
    }

    public function run(): void
    {
        $input  = Factory::getApplication()->getInput();
        $formId = $input->getInt('id', 0);

        if ($formId <= 0) {
            Factory::getApplication()->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
            return;
        }

        Factory::getApplication()->redirect(Route::_(
            'index.php?option=com_breezingformsng&ff_form=' . $formId,
            false
        ));
    }

    private function togglePublish(int $state): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            if (!empty($ids)) {
                $this->getFormModel()->publish($ids, $state);
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    private function moveOrder(int $inc): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$this->checkToken()) {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        } else {
            $ids = (array) $input->get('cid', [], 'INT');
            $pkg = $input->getString('pkg', '');
            if (!empty($ids)) {
                $this->getFormModel()->moveOrder((int) $ids[0], $inc, $pkg);
            }
        }

        $app->redirect(Route::_($this->listUrl($input->getString('pkg', '')), false));
    }

    private function listUrl(string $pkg): string
    {
        return 'index.php?option=com_breezingformsng&view=forms'
            . ($pkg !== '' ? '&pkg=' . rawurlencode($pkg) : '');
    }

    private function getFormModel(): FormModel
    {
        $model = Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Form', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof FormModel) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        return $model;
    }
}
