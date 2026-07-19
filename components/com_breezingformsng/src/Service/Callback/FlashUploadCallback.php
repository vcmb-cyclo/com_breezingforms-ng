<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

/**
 * Chunked (legacy flash) upload endpoint (flashUpload=1).
 */
final class FlashUploadCallback
{
    public function __construct(
        private readonly CMSApplication $application,
        private readonly DatabaseInterface $database,
    ) {
    }

    public function handle(): void
    {
        $db = $this->database;


    if (!function_exists('bfProcess')) {
    function bfProcess(&$dataObject, $finaltargetFile, $parent = null, $index = 0, $childrenLength = 0)
    {
        $mdata = $dataObject['properties'];
        if ($mdata['type'] == 'element') {
            switch ($mdata['bfType']) {
                case 'bfFile':
                    if (isset($mdata['flashUploaderBytes']) && intval($mdata['flashUploaderBytes']) > 0 && isset($mdata['bfName']) && trim($mdata['bfName']) == trim($this->application->getInput()->getString('itemName', ''))) {
                        if (file_exists($finaltargetFile) && @filesize($finaltargetFile) > intval($mdata['flashUploaderBytes'])) {
                            @File::delete($finaltargetFile);
                            echo trim($mdata['label']) . ': ' . Text::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_TOO_LARGE');
                            exit;
                        }
                        break;
                    }
                    break;
            }
        }
        if (isset($dataObject['children']) && count($dataObject['children']) != 0) {
            $childrenAmount = count($dataObject['children']);
            for ($i = 0; $i < $childrenAmount; $i++) {
                bfProcess($dataObject['children'][$i], $finaltargetFile, $mdata, $i, $childrenAmount);
            }
        }
    }
    }

    @ob_end_clean();
    $input = $this->application->getInput();
    if (is_numeric($input->getString('form', '')) && !empty($_FILES) && $input->getString('bfFlashUploadTicket', '') != '') {

        $formId = $input->getInt('form', -1);
        $itemName = $input->getString('itemName', '');
        $query = $db->getQuery(true)
            ->select(['form.id', 'form.template_code_processed', 'form.template_code'])
            ->from($db->quoteName('#__facileforms_forms', 'form'))
            ->from($db->quoteName('#__facileforms_elements', 'element'))
            ->where('form.id = :formId')
            ->where('element.name = :itemName')
            ->where('element.form = :formId2')
            ->bind(':formId', $formId, ParameterType::INTEGER)
            ->bind(':itemName', $itemName, ParameterType::STRING)
            ->bind(':formId2', $formId, ParameterType::INTEGER);
        $db->setQuery($query);
        $objectList = $db->loadObjectList();
        $formIdCount = count($objectList);
        if ($formIdCount > 0) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = JPATH_SITE . '/components/com_breezingformsng/uploads/';
            if (@file_exists($targetPath) && @is_dir($targetPath)) {
                $secureTicket = $this->application->getSession()->get('secure_ticket', '', 'com_breezingformsng');
                if ($secureTicket == '') {
                    mt_srand();
                    $secureTicket = md5(strtotime('now') . mt_rand(0, mt_getrandmax()));
                    $this->application->getSession()->set('secure_ticket', $secureTicket, 'com_breezingformsng');
                }

                $targetFile = str_replace('//', '/', $targetPath) . 'chunks/' . $input->getInt('offset', 0) . '_' . bf_sanitizeFilename($input->getString('name', 'unknown')) . '_' . $input->getString('itemName', '') . '_' . $input->getString('bfFlashUploadTicket', '') . '_' . $secureTicket . '_chunktmp';
                $finaltargetFile = str_replace('//', '/', $targetPath) . bf_sanitizeFilename($input->getString('name', 'unknown')) . '_' . $input->getString('itemName', '') . '_' . $input->getString('bfFlashUploadTicket', '') . '_' . $secureTicket . '_flashtmp';

                if (@File::upload($tempFile, $targetFile)) {

                    $chunky = @file_get_contents($targetFile);

                    // ok, here we try native PHP file operation
                    // to prevent opening and readin the file
                    if (@is_writable(str_replace('//', '/', $targetPath))) {
                        $fp = @fopen($finaltargetFile, 'ab');
                        @fwrite($fp, $chunky);
                        @fclose($fp);
                    } else {
                        // as last resort, we use the
                        // joomla api that uses FTP if possible
                        // and if the folder is not writable
                        // and hope the file is not exceeding the
                        // php memory limit
                        $final = '';
                        if (@file_exists($finaltargetFile)) {
                            $final = @file_get_contents($finaltargetFile);
                        }
                        $newbuf = $final . $chunky;
                        @File::write($finaltargetFile, $newbuf);
                    }

                    $dataObject = json_decode(bf_b64dec($objectList[0]->template_code), true);

                    bfProcess($dataObject, $finaltargetFile);
                    @File::delete($targetFile);
                } else {
                    echo 'Could not upload file ' . addslashes($_FILES['Filedata']['name']) . '!';
                }
            } else {
                echo 'Invalid file storage path for file ' . addslashes($_FILES['Filedata']['name']) . '! Please check the upload folder path and its permissions!';
            }
        } else {
            echo 'Form id and element do not match!';
        }
    }
    exit;
    }
}
