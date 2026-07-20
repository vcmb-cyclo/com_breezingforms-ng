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

    @ob_end_clean();
    $input = $this->application->getInput();
    $uploadedFile = $input->files->get('Filedata', [], 'array');
    if (is_numeric($input->getString('form', '')) && $uploadedFile !== [] && $input->getString('bfFlashUploadTicket', '') != '') {

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
            $tempFile = (string) ($uploadedFile['tmp_name'] ?? '');
            $uploadedName = (string) ($uploadedFile['name'] ?? '');
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

                    $validationError = $this->validateUploadSize($dataObject, $finaltargetFile, $itemName);

                    if ($validationError !== null) {
                        File::delete($finaltargetFile);
                        echo $validationError;
                        $this->application->close();
                    }

                    @File::delete($targetFile);
                } else {
                    echo 'Could not upload file ' . addslashes($uploadedName) . '!';
                }
            } else {
                echo 'Invalid file storage path for file ' . addslashes($uploadedName) . '! Please check the upload folder path and its permissions!';
            }
        } else {
            echo 'Form id and element do not match!';
        }
    }
    $this->application->close();
    }

    private function validateUploadSize(array $dataObject, string $targetFile, string $itemName): ?string
    {
        $metadata = $dataObject['properties'] ?? [];

        if (
            ($metadata['type'] ?? '') === 'element'
            && ($metadata['bfType'] ?? '') === 'bfFile'
            && (int) ($metadata['flashUploaderBytes'] ?? 0) > 0
            && trim((string) ($metadata['bfName'] ?? '')) === trim($itemName)
            && is_file($targetFile)
            && filesize($targetFile) > (int) $metadata['flashUploaderBytes']
        ) {
            return trim((string) ($metadata['label'] ?? '')) . ': '
                . Text::_('COM_BREEZINGFORMSNG_FLASH_UPLOADER_TOO_LARGE');
        }

        foreach (($dataObject['children'] ?? []) as $child) {
            if (!is_array($child)) {
                continue;
            }

            $error = $this->validateUploadSize($child, $targetFile, $itemName);

            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }
}
