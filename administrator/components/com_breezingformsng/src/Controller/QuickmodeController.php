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
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Vcmb\Component\BreezingformsNG\Administrator\Model\QuickmodeModel;

class QuickmodeController extends BaseController
{
    public function display($cachable = false, $urlparams = []): static
    {
        Factory::getApplication()->getInput()->set('view', 'quickmode');
        return parent::display($cachable, $urlparams);
    }

    /**
     * Chunked AJAX save endpoint — called repeatedly by quickmode-app.js.
     * Writes each chunk to a temp file, then on the last chunk reassembles,
     * saves to DB, and echoes the form ID as plain text.
     */
    public function doAjaxSave(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $chunksLength = $input->getInt('chunksLength', 0);
        $form         = $input->getInt('form', 0);
        $chunkIdx     = $input->getInt('chunkIdx', 0);
        $rndAdd       = $input->getString('rndAdd', '0');
        $chunk        = $input->getString('chunk', '');

        $dest = JPATH_SITE . '/media/breezingforms/ajax_cache/ajaxsave_' . $chunkIdx . '_' . $rndAdd . '.txt';
        @File::write($dest, $chunk);

        @ob_end_clean();

        if ($chunkIdx === $chunksLength - 1) {
            $contents = '';
            for ($i = 0; $i < $chunksLength; $i++) {
                $file = JPATH_SITE . '/media/breezingforms/ajax_cache/ajaxsave_' . $i . '_' . $rndAdd . '.txt';
                $contents .= (string) @file_get_contents($file);
                @File::delete($file);
            }

            $formId = $this->getQuickmodeModel()->save($form, (array) json_decode(base64_decode($contents), true));

            // Optional ContentBuilderNG integration
            $cbngBasePath = JPATH_SITE . '/administrator/components/com_contentbuilderng';
            if (is_file($cbngBasePath . '/com_contentbuilderng.xml')) {
                require_once $cbngBasePath . '/src/Helper/FormSourceFactory.php';
                require_once $cbngBasePath . '/src/Service/PathService.php';
                require_once $cbngBasePath . '/src/Service/TemplateSampleService.php';
                require_once $cbngBasePath . '/src/Service/FormSupportService.php';

                $cbForm = \CB\Component\Contentbuilderng\Administrator\Helper\FormSourceFactory::getForm('com_breezingformsng', $formId);
                $db     = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery(
                    'SELECT id FROM #__contentbuilderng_forms WHERE `type` = \'com_breezingformsng\' AND `reference_id` = ' . (int) $formId
                );
                $cbForms = $db->loadColumn();

                if (is_object($cbForm) && count($cbForms)) {
                    $formSupportService = new \CB\Component\Contentbuilderng\Administrator\Service\FormSupportService(
                        new \CB\Component\Contentbuilderng\Administrator\Service\PathService(),
                        $db,
                        new \CB\Component\Contentbuilderng\Administrator\Service\TemplateSampleService($app, $db)
                    );
                    foreach ($cbForms as $dataId) {
                        $formSupportService->synchElements($dataId, $cbForm);
                    }
                }
            }

            ob_start();
            echo $formId;
            exit;
        }

        exit;
    }

    /**
     * Inline editor for rich-text / CodeMirror element properties (opens in a modal/iframe).
     */
    public function editor(): void
    {
        $input = Factory::getApplication()->getInput();
        $input->set('view', 'quickmode');
        $input->set('layout', 'editor');
        parent::display();
    }

    private function getQuickmodeModel(): QuickmodeModel
    {
        $model = Factory::getApplication()
            ->bootComponent('com_breezingformsng')
            ->getMVCFactory()
            ->createModel('Quickmode', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof QuickmodeModel) {
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        }

        return $model;
    }
}
