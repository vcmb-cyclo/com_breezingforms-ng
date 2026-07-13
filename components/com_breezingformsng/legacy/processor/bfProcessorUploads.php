<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 **/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Language\Text;
use Vcmb\Component\BreezingformsNG\Site\Service\QuickMode\ElementFinder;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\ImageResizer;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadError;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadPathResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadStorage;

/**
 * Upload storage, image resizing and QuickMode element lookup.
 */
trait bfProcessorUploads
{
    private ?ImageResizer $imageResizerService = null;
    private ?UploadPathResolver $uploadPathResolverService = null;
    private ?UploadStorage $uploadStorageService = null;
    private ?ElementFinder $quickModeElementFinderService = null;

    function saveUpload($filename, $userfile_name, $destpath, $timestamp, $useUrl = false, $useUrlDownloadDirectory = '', $resize_target_width = 0, $resize_target_height = 0, $resize_type = '', $resize_bgcolor = '#ffffff', $field_name = '')
    {
        global $mosConfig_fileperms;

        if ($this->dying)
            return '';

        $identity = $this->app->getIdentity();
        $resolvedPath = $this->uploadPathResolver()->resolve(
            (string) $destpath,
            (string) $userfile_name,
            $this->findtags,
            $this->replacetags,
            $this->rows,
            (string) $this->submitted,
            (string) $this->app->get('offset'),
            [
                'username' => $identity->get('username'),
                'id' => $identity->get('id'),
                'name' => $identity->get('name'),
            ]
        );
        $filemode = null;
        if (isset($mosConfig_fileperms)) {
            if ($mosConfig_fileperms != '') {
                $filemode = octdec($mosConfig_fileperms);
            }
        } else {
            $filemode = 0644;
        }

        $result = $this->uploadStorage()->store(
            (string) $filename,
            $resolvedPath['directory'],
            $resolvedPath['filename'],
            (bool) $this->app->getSession()->get('bfFileUploadOverride', true),
            $filemode,
            (bool) $useUrl,
            (int) $resize_target_width,
            (int) $resize_target_height,
            (string) $resize_type,
            $resize_bgcolor === null ? null : (string) $resize_bgcolor
        );

        if (!$result->isSuccessful()) {
            $this->status = _FF_STATUS_UPLOAD_FAILED;
            $this->message = Text::_(match ($result->error) {
                UploadError::DirectoryMissing => 'COM_BREEZINGFORMSNG_PROCESS_DIRNOTEXISTS',
                UploadError::FileExists => 'COM_BREEZINGFORMSNG_PROCESS_FILEEXISTS',
                UploadError::MoveFailed => 'COM_BREEZINGFORMSNG_PROCESS_FILEMOVEFAILED',
                UploadError::ChmodFailed => 'COM_BREEZINGFORMSNG_PROCESS_FILECHMODFAILED',
            });

            return '';
        }

        return ['default' => $result->path, 'server' => $result->serverPath];
    }

    public function exifImageType($filename)
    {
        return $this->imageResizer()->imageType((string) $filename);
    }

    public function resizeFile($path, $width, $height, $bgcolor = '#ffffff', $type = '')
    {
        $this->imageResizer()->resizeFile(
            (string) $path,
            (int) $width,
            (int) $height,
            $bgcolor === null ? null : (string) $bgcolor,
            (string) $type
        );
    }

    public function resize_image($source_image, $destination_width, $destination_height, $type = 0, $bgcolor = array(0, 0, 0))
    {
        return $this->imageResizer()->resizeImage(
            $source_image,
            (int) $destination_width,
            (int) $destination_height,
            (int) $type,
            (array) $bgcolor
        );
    }

    public function returnBytes($val)
    {
        return $this->imageResizer()->parseByteSize((string) $val);
    }

    public function findQuickModeElement(array $dataObject, $needle)
    {
        return $this->quickModeElementFinder()->find($dataObject, (string) $needle);
    }

    // saveUpload

    public function measureTime()
    {
        $a = explode(' ', microtime());
        return ((double) $a[0] + $a[1]) / 1000;
    }

    private function imageResizer(): ImageResizer
    {
        return $this->imageResizerService ??= new ImageResizer();
    }

    private function uploadPathResolver(): UploadPathResolver
    {
        return $this->uploadPathResolverService ??= new UploadPathResolver($this->app->getInput());
    }

    private function uploadStorage(): UploadStorage
    {
        return $this->uploadStorageService ??= new UploadStorage($this->imageResizer());
    }

    private function quickModeElementFinder(): ElementFinder
    {
        return $this->quickModeElementFinderService ??= new ElementFinder();
    }

}
