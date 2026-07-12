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

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Filesystem\Path;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Log\Log;
use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\FormSourceFactory;
use CB\Component\Contentbuilderng\Administrator\Service\ArticleService;
use CB\Component\Contentbuilderng\Administrator\Service\ListSupportService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;

/**
 * Upload storage, image resizing and QuickMode element lookup.
 */
trait bfProcessorUploads
{
    function saveUpload($filename, $userfile_name, $destpath, $timestamp, $useUrl = false, $useUrlDownloadDirectory = '', $resize_target_width = 0, $resize_target_height = 0, $resize_type = '', $resize_bgcolor = '#ffffff', $field_name = '')
    {
        global $ff_config, $mosConfig_fileperms;

        if ($this->dying)
            return '';

        $tz = 'UTC';
        $tz = new DateTimeZone($this->app->get('offset'));

        $date_stamp = date('Y_m_d_H_i_s');
        $date_ = new \Joomla\CMS\Date\Date($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp = $date_->format('Y_m_d_H_i_s', true);

        $date_stamp2 = date('Y_m_d');
        $date_ = new \Joomla\CMS\Date\Date($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp2 = $date_->format('Y_m_d', true);

        $baseDir = Path::clean(str_replace($this->findtags, $this->replacetags, $destpath));

        // test if there is a filemask and remove it from the basepath
        $_baseDir = $baseDir;
        $fmtest = str_replace('{filemask:', '', basename($baseDir));
        if ($fmtest != basename($baseDir)) {
            $baseDir = rtrim(rtrim(str_replace(basename($baseDir), '', $baseDir), '/'), "\\");
        }

        if (!file_exists($baseDir)) {
            $this->status = _FF_STATUS_UPLOAD_FAILED;
            $this->message = Text::_('COM_BREEZINGFORMSNG_PROCESS_DIRNOTEXISTS');
            return '';
        } // if

        if ($fmtest != basename($_baseDir)) {
            $fm = basename($_baseDir);
            foreach ($this->rows as $row) {
                $fname = BFRequest::getVar('ff_nm_' . $row->name, array(), 'POST', 'HTML', BFREQUEST_ALLOWHTML);

                foreach ($fname as $_fname) {
                    $fm = str_replace('{filemask:' . strtolower($row->name) . '}', File::makeSafe(trim($_fname)), $fm);
                    // so it works the same like for folders
                    $fm = str_replace('{' . strtolower($row->name) . ':value}', File::makeSafe(trim($_fname)), $fm);
                }
            }
            $fm = str_replace('{filemask:_separator}', '_', $fm);
            $fm = str_replace('{filemask:_username}', trim(Factory::getApplication()->getIdentity()->get('username')), $fm);
            $fm = str_replace('{filemask:_userid}', trim(Factory::getApplication()->getIdentity()->get('id')), $fm);
            $fm = str_replace('{filemask:_name}', trim(Factory::getApplication()->getIdentity()->get('name')), $fm);
            $fm = str_replace('{filemask:_datetime}', trim($date_stamp), $fm);
            $fm = str_replace('{filemask:_date}', trim($date_stamp2), $fm);
            $fm = str_replace('{filemask:_timestamp}', trim(time()), $fm);
            $fm = str_replace('{filemask:_random}', trim(mt_rand(0, mt_getrandmax())), $fm);
            $fm = str_replace('{filemask:_filename}', trim(basename($userfile_name, '.' . File::getExt($userfile_name))), $fm);
            if ($fm == '') {
                $fm = '__empty__';
            }
            $userfile_name = $fm . '.' . File::getExt($userfile_name);
        }

        //if ($timestamp)
        //	$userfile_name = $date_stamp . '_' . $userfile_name;
        $path = $baseDir . '/' . $userfile_name;
        //if ($timestamp) $path .= '.'.date('YmdHis');
        if (file_exists($path) && $this->app->getSession()->get('bfFileUploadOverride', true)) {
            $rnd = md5(mt_rand(0, mt_getrandmax()));
            $path = $baseDir . '/' . $rnd . '_' . $userfile_name;
            //if ($timestamp) $path .= '.'.date('YmdHis');
            if (file_exists($path)) {
                $this->status = _FF_STATUS_UPLOAD_FAILED;
                $this->message = Text::_('COM_BREEZINGFORMSNG_PROCESS_FILEEXISTS');
                return '';
            }
        } else if (file_exists($path) && !$this->app->getSession()->get('bfFileUploadOverride', true)) {
            unlink($path);
        }

        if (!move_uploaded_file($filename, $path)) {
            $this->status = _FF_STATUS_UPLOAD_FAILED;
            $this->message = Text::_('COM_BREEZINGFORMSNG_PROCESS_FILEMOVEFAILED');
            return '';
        } // if

        $filemode = NULL;
        if (isset($mosConfig_fileperms)) {
            if ($mosConfig_fileperms != '')
                $filemode = octdec($mosConfig_fileperms);
        } else
            $filemode = 0644;
        if (isset($filemode)) {
            if (!@chmod($path, $filemode)) {
                $this->status = _FF_STATUS_UPLOAD_FAILED;
                $this->message = Text::_('COM_BREEZINGFORMSNG_PROCESS_FILECHMODFAILED');
                return '';
            } // if
        } // if

        $serverPath = $path;
        if ($useUrl) {

            $cleaned = str_replace(JPATH_SITE . '/', '', $baseDir);

            $path = Uri::root() . rtrim($cleaned, '/') . '/' . basename($path);
        }

        // resize if image
        // last param = crop or simple. Nothing for exact.
        if (intval($resize_target_height) > 0 && intval($resize_target_width) > 0) {
            $this->resizeFile($serverPath, intval($resize_target_width), intval($resize_target_height), $resize_bgcolor, $resize_type);
        }
        return array('default' => $path, 'server' => $serverPath);
    }

    public function exifImageType($filename)
    {
        // some hosting providers think it is a good idea not to compile in exif with php...
        if (!function_exists('exif_imagetype')) {
            if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
                return $type;
            }
            return false;
        } else {
            return exif_imagetype($filename);
        }
    }

    public function resizeFile($path, $width, $height, $bgcolor = '#ffffff', $type = '')
    {
        $image = @getimagesize($path);

        if ($image !== false) {

            /*
              if($image[0] > 16384){
              return;
              }

              if($image[1] > 16384){
              return;
              } */

            $col_ = $bgcolor;
            if ($bgcolor !== null) {
                $col = array();
                $col[0] = intval(@hexdec(@substr($bgcolor, 1, 2)));
                $col[1] = intval(@hexdec(@substr($bgcolor, 3, 2)));
                $col[2] = intval(@hexdec(@substr($bgcolor, 5, 2)));
                $col_ = $col;
            }
            $exif_type = $this->exifImageType($path);
            // try to prevent memory issues
            $memory = true;

            $imageInfo = $image;

            $MB = 1048576;
            $K64 = 65536;
            $TWEAKFACTOR = 1.5;
            $channels = isset($image['channels']) ? $image['channels'] : 0;
            $memoryNeeded = round(
                ($image[0] * $image[1] * $image['bits'] * ($channels / 8) + $K64
                ) * $TWEAKFACTOR
            );

            $ini = 8 * $MB;
            if (ini_get('memory_limit') !== false) {
                $ini = $this->returnBytes(ini_get('memory_limit'));
            }
            $memoryLimit = $ini;
            if (
                function_exists('memory_get_usage') &&
                memory_get_usage() + $memoryNeeded > $memoryLimit
            ) {
                $memory = false;
            }
            if ($memory) {
                switch ($exif_type) {
                    case IMAGETYPE_JPEG2000:
                    case IMAGETYPE_JPEG:
                        $resource = @imagecreatefromjpeg($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ($type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagejpeg($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                File::write($path, $buffer);
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                    case IMAGETYPE_GIF:
                        $resource = @imagecreatefromgif($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ($type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagegif($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                File::write($path, $buffer);
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                    case IMAGETYPE_PNG:
                        $resource = @imagecreatefrompng($path);
                        if ($resource) {
                            $resized = @$this->resize_image($resource, $width, $height, $type == 'crop' ? 1 : ($type == 'simple' ? 3 : 2), $col_);
                            if ($resized) {
                                ob_start();
                                @imagepng($resized);
                                $buffer = ob_get_contents();
                                ob_end_clean();
                                File::write($path, $buffer);
                                @imagedestroy($resized);
                            }
                            @imagedestroy($resource);
                        }
                        break;
                }
            }
        }
    }

    public function resize_image($source_image, $destination_width, $destination_height, $type = 0, $bgcolor = array(0, 0, 0))
    {
        // $type (1=crop to fit, 2=letterbox)
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);
        $source_ratio = $source_width / $source_height;
        if ($destination_height == 0 && $type == 3) {
            $destination_height = $source_height;
        }
        $destination_ratio = $destination_width / $destination_height;
        if ($type == 3) {

            $old_width = $source_width;
            $old_height = $source_height;

            // Target dimensions
            $max_width = $destination_width;
            $max_height = $destination_height;
            // Get current dimensions
            // Calculate the scaling we need to do to fit the image inside our frame
            $scale = min($max_width / $old_width, $max_height / $old_height);

            // Get the new dimensions
            $destination_width = ceil($scale * $old_width);
            $destination_height = ceil($scale * $old_height);

            $new_destination_width = $destination_width;
            $new_destination_height = $destination_height;

            $source_x = 0;
            $source_y = 0;
            $destination_x = 0;
            $destination_y = 0;
        } else if ($type == 1) {
            // crop to fit
            if ($source_ratio > $destination_ratio) {
                // source has a wider ratio
                $temp_width = (int) ($source_height * $destination_ratio);
                $temp_height = $source_height;
                $source_x = (int) (($source_width - $temp_width) / 2);
                $source_y = 0;
            } else {
                // source has a taller ratio
                $temp_width = $source_width;
                $temp_height = (int) ($source_width * $destination_ratio);
                $source_x = 0;
                $source_y = (int) (($source_height - $temp_height) / 2);
            }
            $destination_x = 0;
            $destination_y = 0;
            $source_width = $temp_width;
            $source_height = $temp_height;
            $new_destination_width = $destination_width;
            $new_destination_height = $destination_height;
        } else {
            // letterbox
            if ($source_ratio < $destination_ratio) {
                // source has a taller ratio
                $temp_width = (int) ($destination_height * $source_ratio);
                $temp_height = $destination_height;
                $destination_x = (int) (($destination_width - $temp_width) / 2);
                $destination_y = 0;
            } else {
                // source has a wider ratio
                $temp_width = $destination_width;
                $temp_height = (int) ($destination_width / $source_ratio);
                $destination_x = 0;
                $destination_y = (int) (($destination_height - $temp_height) / 2);
            }
            $source_x = 0;
            $source_y = 0;
            $new_destination_width = $temp_width;
            $new_destination_height = $temp_height;
        }
        $destination_image = imagecreatetruecolor($destination_width, $destination_height);
        if ($type == 2) {
            imagefill($destination_image, 0, 0, imagecolorallocate($destination_image, $bgcolor[0], $bgcolor[1], $bgcolor[2]));
        }
        imagecopyresampled($destination_image, $source_image, $destination_x, $destination_y, $source_x, $source_y, $new_destination_width, $new_destination_height, $source_width, $source_height);
        return $destination_image;
    }

    public function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = str_replace($val[strlen($val) - 1], '', $val);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024 * 1048576;
                break;
            case 'm':
                $val *= 1048576;
                break;
            case 'k':
                $val *= 1024;
                break;
            default:
                $val = 8 * 1048576;
        }

        return $val;
    }

    public function findQuickModeElement(array $dataObject, $needle)
    {

        if ($dataObject['properties']['type'] == 'element' && isset($dataObject['properties']['bfName']) && $dataObject['properties']['bfName'] == $needle) {
            return $dataObject;
        }

        if (isset($dataObject['children']) && count($dataObject['children']) != 0) {

            $childrenAmount = count($dataObject['children']);

            for ($i = 0; $i < $childrenAmount; $i++) {
                $child = $this->findQuickModeElement($dataObject['children'][$i], $needle);
                if ($child !== null) {
                    return $child;
                }
            }
        }
        return null;
    }

    // saveUpload

    public function measureTime()
    {
        $a = explode(' ', microtime());
        return ((double) $a[0] + $a[1]) / 1000;
    }

}
