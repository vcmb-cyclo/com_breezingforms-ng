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
use Joomla\CMS\Filter\InputFilter;
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
use Joomla\CMS\Mail\MailerFactoryInterface;
use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\FormSourceFactory;
use CB\Component\Contentbuilderng\Administrator\Service\ArticleService;
use CB\Component\Contentbuilderng\Administrator\Service\ListSupportService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;

/**
 * Submit data collection, submission pipeline and HTML sanitizing.
 */
trait bfProcessorSubmission
{
    function collectSubmitdata($cbResult = null)
    {
        if ($this->dying || $this->submitdata)
            return;

        $this->submitdata = array();
        $this->savedata = array();
        $this->maildata = array();
        $this->sfdata = array();
        $this->xmldata = array();
        $names = array();
        if (count($this->rows)) {
            $time_passed = 0;
            $start_time = $this->measureTime();
            $max_exec_time = 15;
            if (function_exists('ini_get')) {
                $max_exec_time = @ini_get('max_execution_time');
            }
            $max_time = !empty($max_exec_time) ? intval($max_exec_time) / 2 : 15;
            foreach ($this->rows as $row) {
                if (!in_array($row->name, $names)) {
                    switch ($row->type) {
                        case 'File Upload':

                            // CONTENTBUILDER
                            if ($cbResult !== null && isset($cbResult['data']) && $cbResult['data'] != null) {
                                $rowdata1 = Path::clean(str_replace($this->findtags, $this->replacetags, $row->data1));
                                if ($cbResult['data']['protect_upload_directory']) {
                                    if (is_dir($rowdata1) && !file_exists($rowdata1 . '/' . '.htaccess'))
                                        File::write($rowdata1 . '/' . '.htaccess', $def = 'deny from all');
                                } else {
                                    if (is_dir($rowdata1) && file_exists($rowdata1 . '/' . '.htaccess'))
                                        File::delete($rowdata1 . '/' . '.htaccess');
                                }
                            }

                            $areas = json_decode($this->formrow->template_areas, true);
                            $useUrl = false;
                            $useUrlDownloadDirectory = '';
                            $resize_target_width = 0;
                            $resize_target_height = 0;
                            $resize_type = '';
                            $resize_bgcolor = '#ffffff';
                            if (trim($this->formrow->template_code_processed) == 'QuickMode' && is_array($areas)) {
                                foreach ($areas as $area) { // don't worry, size is only 1 in QM
                                    if (isset($area['elements'])) {
                                        foreach ($area['elements'] as $element) {
                                            if (isset($element['options']) && isset($element['options']['useUrl']) && isset($element['name']) && trim($element['name']) == trim($row->name) && isset($element['internalType']) && $element['internalType'] == 'bfFile') {
                                                $useUrl = $element['options']['useUrl'];
                                                $useUrlDownloadDirectory = $element['options']['useUrlDownloadDirectory'];
                                                $resize_target_width = $element['options']['resize_target_width'];
                                                $resize_target_height = $element['options']['resize_target_height'];
                                                $resize_type = $element['options']['resize_type'];
                                                $resize_bgcolor = $element['options']['resize_bgcolor'];
                                                break;
                                            }
                                        }
                                    }
                                    break; // just in case
                                }
                            }

                            $uploadfiles = isset($_FILES['ff_nm_' . $row->name]) ? $_FILES['ff_nm_' . $row->name] : null;

                            if ($this->formrow->template_code != '' && isset($_FILES['ff_nm_' . $row->name]) && $_FILES['ff_nm_' . $row->name]['tmp_name'][0] != '' && trim($row->data2) != '') {
                                $fileName = $_FILES['ff_nm_' . $row->name]['name'][0];
                                $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                                $allowedExtensions = explode(',', strtolower(str_replace(' ', '', trim($row->data2))));

                                if (!in_array($ext, $allowedExtensions)) {
                                    $this->status = _FF_STATUS_FILE_EXTENSION_NOT_ALLOWED;
                                    return;
                                }
                            }

                            $paths = array();
                            $serverPaths = array();
                            // CONTENTBUILDER
                            $is_relative = array();

                            if ($uploadfiles) {
                                $name = $uploadfiles['name'];
                                $tmp_name = $uploadfiles['tmp_name'];
                                $cnt = count($name);
                                for ($i = 0; $i < $cnt; $i++) {
                                    $path = '';
                                    if ($name[$i] != '') {
                                        $rowpath1 = $row->data1;
                                        //if ($cbResult !== null && isset($cbResult['data']) && $cbResult['data'] != null) {
                                        $rowpath1 = $this->cbCreatePathByTokens($rowpath1, $this->rows, $row->name);
                                        //}

                                        $pathInfo = $this->saveUpload($tmp_name[$i], bf_sanitizeFilename($name[$i]), $rowpath1, $row->flag1, $useUrl, $useUrlDownloadDirectory, $resize_target_width, $resize_target_height, $resize_type, $resize_bgcolor, $row->name);
                                        $path = $pathInfo['default'];
                                        $serverPath = $pathInfo['server'];
                                        if ($this->status != _FF_STATUS_OK)
                                            return;
                                        $paths[] = $path;

                                        $serverPaths[] = $serverPath;
                                        $this->submitdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);
                                        // CONTENTBUILDER
                                        if (strpos(strtolower($row->data1), '{cbsite}') === 0) {
                                            $is_relative[$serverPath] = true;
                                        }
                                    } // if
                                } // for
                            } // if
                            if (Factory::getApplication()->getInput()->getString('bfFlashUploadTicket', '') != '') {
                                $tickets = $this->app->getSession()->get('bfFlashUploadTickets', array());
                                mt_srand();
                                if (isset($tickets[Factory::getApplication()->getInput()->getString('bfFlashUploadTicket', (string) mt_rand(0, mt_getrandmax()))])) {
                                    $sourcePath = JPATH_SITE . '/components/com_breezingformsng/uploads/';
                                    if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath)) {

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

                                        // trying glob instead of readdir()

                                        foreach (glob($sourcePath . '*') as $glob_file) {

                                            $file = basename($glob_file);

                                            if ($file != "." && $file != "..") {
                                                $parts = explode('_', $file);
                                                if (count($parts) >= 5) {
                                                    if ($parts[count($parts) - 1] == 'flashtmp') {

                                                        if ($parts[count($parts) - 3] == Factory::getApplication()->getInput()->getString('bfFlashUploadTicket', '')) {


                                                            if ($parts[count($parts) - 4] == $row->name) {

                                                                unset($parts[count($parts) - 1]);
                                                                unset($parts[count($parts) - 1]);
                                                                unset($parts[count($parts) - 1]);
                                                                unset($parts[count($parts) - 1]);
                                                                $userfile_name = implode('_', $parts);
                                                                $rowpath1 = $row->data1;
                                                                //if ($cbResult !== null && isset($cbResult['data']) && $cbResult['data'] != null) {
                                                                $rowpath1 = $this->cbCreatePathByTokens($rowpath1, $this->rows, $row->name);
                                                                //}
                                                                $baseDir = Path::clean(str_replace($this->findtags, $this->replacetags, $rowpath1));

                                                                // test if there is a filemask and remove it from the basepath
                                                                $_baseDir = $baseDir;

                                                                $fmtest = str_replace('{filemask:', '', basename($baseDir));
                                                                if ($fmtest != basename($baseDir)) {
                                                                    $baseDir = rtrim(rtrim(str_replace(basename($baseDir), '', $baseDir), '/'), "\\");
                                                                }

                                                                if ($fmtest != basename($_baseDir)) {
                                                                    $fm = basename($_baseDir);

                                                                    foreach ($this->rows as $row2) {

                                                                        $rawFname = Factory::getApplication()->getInput()->post->get('ff_nm_' . $row2->name, [], 'raw');
                                                        $permissiveFilter = InputFilter::getInstance([], [], 1, 1);
                                                        $fname = \is_array($rawFname)
                                                            ? array_map(static fn ($value) => $permissiveFilter->clean((string) $value, 'html'), $rawFname)
                                                            : [];

                                                                        foreach ($fname as $_fname) {
                                                                            $fm = str_replace('{filemask:' . strtolower($row2->name) . '}', File::makeSafe(trim($_fname)), $fm);
                                                                            // so it works the same like for folders
                                                                            $fm = str_replace('{' . strtolower($row2->name) . ':value}', File::makeSafe(trim($_fname)), $fm);
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

                                                                //if ($row->flag1)
                                                                //	$userfile_name = $date_stamp . '_' . $userfile_name;
                                                                $path = $baseDir . '/' . $userfile_name;
                                                                //if ($row->flag1) $path .= '.'.date('YmdHis');
                                                                if (file_exists($path) && $this->app->getSession()->get('bfFileUploadOverride', true)) {
                                                                    $rnd = md5(mt_rand(0, mt_getrandmax()));
                                                                    $path = $baseDir . '/' . $rnd . '_' . $userfile_name;
                                                                    //if ($row->flag1) $path .= '.'.date('YmdHis');
                                                                    if (file_exists($path)) {
                                                                        $this->status = _FF_STATUS_UPLOAD_FAILED;
                                                                        $this->message = Text::_('COM_BREEZINGFORMSNG_PROCESS_FILEEXISTS');
                                                                        return '';
                                                                    }
                                                                } else if (file_exists($path) && !$this->app->getSession()->get('bfFileUploadOverride', true)) {
                                                                    unlink($path);
                                                                }

                                                                $ext = strtolower(substr($userfile_name, strrpos($userfile_name, '.') + 1));
                                                                $allowedExtensions = explode(',', strtolower(str_replace(' ', '', trim($row->data2))));

                                                                if (!in_array($ext, $allowedExtensions)) {
                                                                    $this->status = _FF_STATUS_FILE_EXTENSION_NOT_ALLOWED;
                                                                }

                                                                if ($this->status != _FF_STATUS_OK)
                                                                    return;

                                                                if (@is_readable($sourcePath . $file) && @file_exists($baseDir) && @is_dir($baseDir)) {
                                                                    @File::copy($sourcePath . $file, $path);
                                                                } else {
                                                                    $this->status = _FF_STATUS_UPLOAD_FAILED;
                                                                    $this->message = Text::_('COM_BREEZINGFORMSNG_PROCESS_FILEMOVEFAILED');
                                                                    return;
                                                                }
                                                                @File::delete($sourcePath . $file);

                                                                $serverPath = $path;

                                                                if ($useUrl) {

                                                                    $cleaned = str_replace(JPATH_SITE . '/', '', $baseDir);

                                                                    $path = Uri::root() . rtrim($cleaned, '/') . '/' . basename($path);
                                                                }

                                                                $paths[] = $path;
                                                                $serverPaths[] = $serverPath;
                                                                $this->submitdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);

                                                                // resize if image
                                                                // last param = crop or simple. Nothing for exact.
                                                                if (intval($resize_target_height) > 0 && intval($resize_target_width) > 0) {
                                                                    $this->resizeFile($serverPath, intval($resize_target_width), intval($resize_target_height), $resize_bgcolor, $resize_type);
                                                                }

                                                                // CONTENTBUILDER
                                                                if (strpos(strtolower($row->data1), '{cbsite}') === 0) {
                                                                    $is_relative[$serverPath] = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if (!count($paths))
                                $paths = array();
                            if ($row->logging == 1) {
                                // db and attachment
                                // DROPBOX SUPPORT request v2 API

                                require_once JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/dropbox/v2/autoload.php';

                                foreach ($serverPaths as $serverPath) {

                                    // DROPBOX File Upload
                                    if ($this->formrow->dropbox_email) {

                                        Alorel\Dropbox\Operation\AbstractOperation::setDefaultToken($this->formrow->dropbox_email);
                                        $upload = new Alorel\Dropbox\Operation\Files\Upload();
                                        $file_to_upload = fopen($serverPath, "rb");
                                        $upload->raw(
                                            '/' . ($this->formrow->dropbox_folder != '' ? $this->formrow->dropbox_folder : $this->formrow->name) . '/' . basename($serverPath),
                                            $file_to_upload
                                        );
                                        try {
                                            fclose($file_to_upload);
                                        } catch (Error $e) {
                                        }
                                    }

                                    // CONTENTBUILDER: to keep the relative path with prefix
                                    $savedata_path = $serverPath;
                                    foreach ($this->findtags as $tag) {
                                        if (strtolower($tag) == '{cbsite}' && isset($is_relative[$serverPath]) && $is_relative[$serverPath]) {
                                            $savedata_path = Path::clean(str_replace(array(JPATH_SITE, JPATH_SITE), array('{cbsite}', '{CBSite}'), $savedata_path));
                                        }
                                    }

                                    if (
                                        ($this->formrow->dblog == 1 && $savedata_path != '') ||
                                        $this->formrow->dblog == 2 || ($cbResult != null && $cbResult['record'] != null)
                                    )
                                        $this->savedata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $savedata_path);
                                }

                                foreach ($paths as $path) {
                                    if (
                                        (($this->formrow->emaillog == 1 && $this->trim($path)) ||
                                            $this->formrow->emaillog == 2) && ($this->formrow->emailxml == 1 ||
                                            $this->formrow->emailxml == 2 || $this->formrow->emailxml == 3 || $this->formrow->emailxml == 4)
                                    )
                                        $this->xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);
                                    if (
                                        (($this->formrow->emaillog == 1 && $this->trim($path)) ||
                                            $this->formrow->mb_emaillog == 2) && ($this->formrow->mb_emailxml == 1 ||
                                            $this->formrow->mb_emailxml == 2 || $this->formrow->mb_emailxml == 3 || $this->formrow->mb_emailxml == 4)
                                    )
                                        $this->mb_xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $path);
                                } // foreach

                                if (!count($paths)) {
                                    if (
                                        ($this->formrow->dblog == 1) ||
                                        $this->formrow->dblog == 2
                                    )
                                        $this->savedata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, '');
                                    if (
                                        $this->formrow->emaillog == 2 && ($this->formrow->emailxml == 1 ||
                                            $this->formrow->emailxml == 2 || $this->formrow->emailxml == 3 || $this->formrow->emailxml == 4)
                                    )
                                        $this->xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, '');
                                    if (
                                        $this->formrow->mb_emaillog == 2 && ($this->formrow->mb_emailxml == 1 ||
                                            $this->formrow->mb_emailxml == 2 || $this->formrow->mb_emailxml == 3 || $this->formrow->mb_emailxml == 4)
                                    )
                                        $this->mb_xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, '');
                                }
                                // mail
                                $paths = implode(nl(), $paths);
                                $serverPaths = implode(nl(), $serverPaths);

                                if ($this->trim($paths)) {
                                    $this->sfadata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $paths, $serverPaths);
                                }

                                if (
                                    ($this->formrow->emaillog == 1 && $this->trim($paths)) ||
                                    $this->formrow->emaillog == 2
                                ) {
                                    $this->maildata[] = array(
                                        $row->id,
                                        $row->name,
                                        strip_tags($row->title),
                                        $row->type,
                                        $paths,
                                        $serverPaths
                                    );
                                }
                            } // if
                            break;
                        case 'Text':
                        case 'Textarea':
                        case 'Checkbox':
                        case 'Radio Button':
                        case 'Select List':
                        case 'Query List':
                        case 'Radio Group':
                        case 'Checkbox Group':
                        case 'Number Input':
                        case 'Calendar':
                        case 'Hidden Input':
                        case 'Signature':
                            if ($row->logging == 1) {

                                $rawValues = Factory::getApplication()->getInput()->post->get("ff_nm_" . $row->name, [''], 'raw');
                                $permissiveFilter = InputFilter::getInstance([], [], 1, 1);
                                $values = \is_array($rawValues)
                                    ? array_map(static fn ($value) => $permissiveFilter->clean((string) $value, 'html'), $rawValues)
                                    : [''];

                                if ($row->type == 'Textarea') {
                                    require_once(JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php');

                                    if (trim($this->formrow->template_code_processed) == 'QuickMode') {
                                        $dataObject = json_decode(bf_b64dec($this->formrow->template_code), true);
                                        $qmelement = $this->findQuickModeElement($dataObject, $row->name);

                                        if ($qmelement !== null && isset($qmelement['properties']['is_html']) && $qmelement['properties']['is_html']) {
                                            $rawValues = Factory::getApplication()->getInput()->post->get("ff_nm_" . $row->name, [''], 'raw');
                                            $permissiveFilter = InputFilter::getInstance([], [], 1, 1);
                                            $values = \is_array($rawValues)
                                                ? array_map(static fn ($value) => $permissiveFilter->clean((string) $value, 'html'), $rawValues)
                                                : [''];

                                            $html_value_cnt = count($values);

                                            for ($html_i = 0; $html_i < $html_value_cnt; $html_i++) {

                                                //$values[$html_i] = $this->removeDangerousHtml($values[$html_i]);

                                                /*
                                                  $input = $this->app->getInput();
                                                  $input->set('cbCleanVar', $values[$html_i]);
                                                  $values[$html_i] = $input->getHtml('cbCleanVar'); */

                                                $values[$html_i] = $permissiveFilter->clean((string) $values[$html_i], 'html');
                                            }
                                        }
                                    }
                                }

                                $sigValues = '';

                                foreach ($values as $value) {

                                    if ($row->type == 'Signature' && $value != '') {
                                        if (!is_dir(JPATH_SITE . '/media/breezingforms/signatures/')) {
                                            Folder::create(JPATH_SITE . '/media/breezingforms/signatures/');
                                            $def = '';
                                            File::write(JPATH_SITE . '/media/breezingforms/signatures/index.html', $def);
                                        }
                                        $sig_decoded = bf_b64dec($value);
                                        $sig_file = JPATH_SITE . '/media/breezingforms/signatures/' . $row->name . '-' . md5($value) . '.png';
                                        File::write($sig_file, $sig_decoded);
                                        $value = basename($sig_file);

                                        $sigValues .= $value;

                                        // DROPBOX SUPPORT request v2 API
                                        if ($this->formrow->dropbox_email) {

                                            require_once JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/dropbox/v2/autoload.php';
                                        }

                                        // DROPBOX Signature upload
                                        if ($this->formrow->dropbox_email) {

                                            Alorel\Dropbox\Operation\AbstractOperation::setDefaultToken($this->formrow->dropbox_email);
                                            $upload = new Alorel\Dropbox\Operation\Files\Upload();
                                            $file_to_upload = fopen($sig_file, "rb");
                                            $upload->raw(
                                                '/' . ($this->formrow->dropbox_folder != '' ? $this->formrow->dropbox_folder : $this->formrow->name) . '/' . basename($sig_file),
                                                $file_to_upload
                                            );
                                            try {
                                                fclose($file_to_upload);
                                            } catch (Error $e) {
                                            }
                                        }
                                    }

                                    // for db
                                    if (
                                        ($this->formrow->dblog == 1 && $value != '') ||
                                        $this->formrow->dblog == 2 || ($cbResult != null && $cbResult['record'] != null)
                                    )
                                        $this->savedata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);

                                    // CONTENTBUILDER
                                    $loadData = true;
                                    switch ($row->type) {
                                        case 'Checkbox':
                                        case 'Checkbox Group':
                                        case 'Radio Button':
                                        case 'Radio Group':
                                        case 'Select List':
                                            if ($value == 'cbGroupMark') {
                                                $loadData = false;
                                            }
                                            break;
                                    }

                                    if ($loadData) {
                                        // submitdata
                                        if ($this->trim($value) != '')
                                            $this->submitdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);

                                        if (
                                            ($this->formrow->emaillog == 1 && $this->trim($value) != '') ||
                                            $this->formrow->emaillog == 2 && (($this->formrow->emailxml == 1 ||
                                                $this->formrow->emailxml == 2 || $this->formrow->emailxml == 3 || $this->formrow->emailxml == 4))
                                        )
                                            $this->xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);
                                        if (
                                            ($this->formrow->mb_emaillog == 1 && $this->trim($value) != '') ||
                                            $this->formrow->mb_emaillog == 2 && (($this->formrow->mb_emailxml == 1 ||
                                                $this->formrow->mb_emailxml == 2 || $this->formrow->mb_emailxml == 3 || $this->formrow->mb_emailxml == 4))
                                        )
                                            $this->mb_xmldata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $value);
                                    }
                                } // foreach
                                // for mail

                                $sfvalues = $values;

                                if ($row->type == 'Signature') {

                                    $values = $sigValues;
                                    $sfvalues = $sigValues;
                                } else if ($row->type == 'Textarea') {

                                    $values = implode(nl(), $values);
                                    $sfvalues = implode(nl(), $sfvalues);
                                } else {

                                    // CONTENTBUILDER
                                    $useNewValues = false;
                                    $newValues = array();
                                    $sfnewValues = array();

                                    foreach ($values as $value) {
                                        switch ($row->type) {
                                            case 'Checkbox':
                                            case 'Checkbox Group':
                                            case 'Radio Button':
                                            case 'Radio Group':
                                            case 'Select List':
                                                if ($value != 'cbGroupMark') {
                                                    $newValues[] = $value;
                                                    $sfnewValues[] = $value;
                                                } else {
                                                    $useNewValues = true;
                                                }
                                                break;
                                        }
                                    }

                                    if ($useNewValues) {
                                        $values = is_array($newValues) ? implode(', ', $newValues) : '';
                                        $sfvalues = is_array($sfnewValues) ? implode(';', $sfnewValues) : '';
                                    } else {
                                        $values = is_array($values) ? implode(', ', $values) : '';
                                        $sfvalues = is_array($sfvalues) ? implode(';', $sfvalues) : '';
                                    }
                                }

                                if ($this->trim($sfvalues)) {
                                    $this->sfdata[] = array($row->id, $row->name, strip_tags($row->title), $row->type, $sfvalues);
                                }

                                if (
                                    ($this->formrow->emaillog == 1 && $this->trim($values)) ||
                                    $this->formrow->emaillog == 2
                                ) {
                                    $this->maildata[] = array(
                                        $row->id,
                                        $row->name,
                                        strip_tags($row->title),
                                        $row->type,
                                        $values
                                    );
                                }
                            } // if logging
                            break;
                        default:
                            ;
                    } // switch
                    $names[] = $row->name;
                } // if
                $time_passed = $this->measureTime();
                if (($time_passed - $start_time) > $max_time) {
                    //break;
                }
            } // for
        }
    }

    // collectSubmitdata

    function submit()
    {
        global $ff_config, $ff_comsite, $ff_mossite, $ff_otherparams;

        if (trim((string) $this->formrow->template_code_processed) !== 'QuickMode') {
            echo '<div class="alert alert-warning">' . Text::_('COM_BREEZINGFORMSNG_QUICKMODE_ONLY') . '</div>';
            return;
        }

        // CONTENTBUILDER BEGIN
        $cbRecordId = 0;
        $cbEmailNotifications = false;
        $cbEmailUpdateNotifications = false;
        $cbResult = $this->cbCheckPermissions();
        if ($cbResult['data'] !== null && $cbResult['data']['email_notifications']) {
            if (!Factory::getApplication()->getInput()->getInt('cb_record_id', 0)) {
                $cbEmailNotifications = true;
            } else {
                $cbEmailNotifications = false;
            }
        }
        if ($cbResult['data'] !== null && $cbResult['data']['email_update_notifications']) {
            if (Factory::getApplication()->getInput()->getInt('cb_record_id', 0)) {
                $cbEmailUpdateNotifications = true;
            } else {
                $cbEmailUpdateNotifications = false;
            }
        }
        if ($cbResult['data'] === null) {
            $cbEmailNotifications = true;
            $cbEmailUpdateNotifications = true;
        }
        // CONTENTBUILDER END
        if (!$this->okrun)
            return;

        ob_start();
        $this->record_id = '';
        $this->status = _FF_STATUS_OK;
        $this->message = '';
        $this->sendNotificationAfterPayment = false;

        // handle Begin Submit piece
        $halt = false;
        $this->collectSubmitdata($cbResult);

        if (!$halt) {

            require_once(JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php');

            $dataObject = json_decode(bf_b64dec($this->formrow->template_code), true);
            $rootMdata = $dataObject['properties'];

            if (Factory::getApplication()->getInput()->getString('ff_applic', '') != 'mod_facileforms' && Factory::getApplication()->getInput()->getInt('ff_frame', 0) != 1 && bf_is_mobile()) {
                $is_device = true;
                $this->isMobile = isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $rootMdata['forceMobile'] ? true : (isset($rootMdata['mobileEnabled']) && isset($rootMdata['forceMobile']) && $rootMdata['mobileEnabled'] && $this->app->getSession()->get('com_breezingformsng.mobile', false) ? true : false);
            } else
                $this->isMobile = false;



            for ($i = 0; $i < $this->rowcount; $i++) {
                $row = $this->rows[$i];
                if ($row->type == "Captcha") {
                    require_once(JPATH_SITE . '/media/com_breezingformsng/images/site/captcha/securimage.php');
                    $securimage = new Securimage();
                    if (!$securimage->check(Factory::getApplication()->getInput()->getString('bfCaptchaEntry', ''))) {
                        $halt = true;
                        $this->status = _FF_STATUS_CAPTCHA_FAILED;
                        exit;
                    }
                    break;
                } else
                    if ($row->type == "ReCaptcha") {


                        $areas = json_decode($this->formrow->template_areas, true);

                        foreach ($areas as $area) {
                            foreach ($area['elements'] as $element) {
                                if ($element['bfType'] == 'ReCaptcha') {

                                    if (!class_exists('ReCaptcha')) {

                                        require_once(JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/recaptcha/newrecaptchalib.php');
                                    }

                                    $reCaptcha = new ReCaptcha($element['privkey']);

                                    $resp = @$reCaptcha->verifyResponse(
                                        $_SERVER["REMOTE_ADDR"],
                                        Factory::getApplication()->getInput()->getString('g-recaptcha-response', '')
                                    );

                                    if ($resp != null && $resp->success) {

                                        // all good
                                    } else {

                                        $halt = true;
                                        $this->status = _FF_STATUS_CAPTCHA_FAILED;
                                        exit;
                                    }

                                    break;
                                }
                            }
                        }

                        break;
                    }
            }


            $areas = json_decode($this->formrow->template_areas, true);

            if (is_array($areas)) {
                switch (Factory::getApplication()->getInput()->getString('ff_payment_method', '')) {
                    case 'Stripe':
                    case 'PayPal':
                    case 'Sofortueberweisung':
                        foreach ($areas as $area) {
                            foreach ($area['elements'] as $element) {
                                if ($element['internalType'] == 'bfStripe' || $element['internalType'] == 'bfPayPal' || $element['internalType'] == 'bfSofortueberweisung') {
                                    $options = $element['options'];
                                    if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                        $this->sendNotificationAfterPayment = true;
                                    }
                                }
                            }
                        }
                }
            }
        }

        if (!$halt) {

            $code = '';

            switch ($this->formrow->piece3cond) {
                case 1: // library
                    $this->database->setQuery(
                        "select name, code from #__facileforms_pieces " .
                        "where id=" . $this->formrow->piece3id . " and published=1 "
                    );
                    $rows = $this->database->loadObjectList();
                    if (count($rows))
                        echo $this->execPiece(
                            $rows[0]->code,
                            Text::_('COM_BREEZINGFORMSNG_PROCESS_BSPIECE') . " " . $rows[0]->name,
                            'p',
                            $this->formrow->piece3id,
                            null
                        );
                    break;
                case 2: // custom code
                    echo $this->execPiece(
                        $this->formrow->piece3code,
                        Text::_('COM_BREEZINGFORMSNG_PROCESS_BSPIECEC'),
                        'f',
                        $this->form,
                        3
                    );
                    break;
                default:
                    break;
            } // switch
            if ($this->bury())
                return;

            if ($this->status == _FF_STATUS_OK) {
                if (!$this->formrow->published) {
                    $this->status = _FF_STATUS_UNPUBLISHED;
                } else {
                    if ($this->status == _FF_STATUS_OK) {
                        if ($this->formrow->dblog > 0)
                            $cbRecordId = $this->logToDatabase($cbResult);

                        if ($this->status == _FF_STATUS_OK) {
                            if ($this->formrow->emailntf > 0 && ($cbEmailNotifications || $cbEmailUpdateNotifications)) { // CONTENTBUILDER
                                $this->sendEmailNotification();
                            }
                            if ($this->formrow->mb_emailntf > 0 && ($cbEmailNotifications || $cbEmailUpdateNotifications)) { // CONTENTBUILDER
                                $this->sendMailbackNotification();
                            }

                            // DROPBOX request v2 API and PDF,CSV, XML upload
                            if ($this->formrow->dropbox_submission_enabled) {
                                if ($this->formrow->dropbox_email) {
                                    try {
                                        require_once JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/dropbox/v2/autoload.php';
                                        // $space = "Dropbox\\Client";
                                        // $dbxClient = new $space($this->formrow->dropbox_email, "BreezingForms/1.8.5");
                                        Alorel\Dropbox\Operation\AbstractOperation::setDefaultToken($this->formrow->dropbox_email);

                                        $dropbox_types = explode(',', $this->formrow->dropbox_submission_types);
                                        foreach ($dropbox_types as $dropbox_type) {
                                            $dropbox_file = '';
                                            switch ($dropbox_type) {
                                                case 'pdf':
                                                    $dropbox_file = $this->exppdf();
                                                    break;
                                                case 'csv':
                                                    $dropbox_file = $this->expcsv();
                                                    break;
                                                case 'xml':
                                                    $dropbox_file = $this->expxml();
                                                    break;
                                            }
                                            if ($dropbox_file != '') {
                                                try {

                                                    $upload = new Alorel\Dropbox\Operation\Files\Upload();
                                                    $file_to_upload = fopen($dropbox_file, "rb");
                                                    $upload->raw(
                                                        '/' . ($this->formrow->dropbox_folder != '' ? $this->formrow->dropbox_folder : $this->formrow->name) . '/' . basename($dropbox_file),
                                                        $file_to_upload
                                                    );
                                                    try {
                                                        fclose($file_to_upload);
                                                    } catch (Error $e) {
                                                    }
                                                } catch (Exception $e) {

                                                }
                                            }
                                        }
                                    } catch (Exception $e) {

                                    }
                                }
                            }


                            $this->sendMailChimpNotification();
                            $this->sendSalesforceNotification();

                            PluginHelper::importPlugin('breezingforms_addons');
                            $dispatcher = $this->app->getDispatcher();
                            $dispatcher->dispatch('onPropertiesExecute', new Joomla\Event\Event('onPropertiesExecute',
                                array(
                                    $this
                                )
                            ));

                            $tickets = $this->app->getSession()->get('bfFlashUploadTickets', array());
                            mt_srand();
                            if (isset($tickets[Factory::getApplication()->getInput()->getString('bfFlashUploadTicket', (string) mt_rand(0, mt_getrandmax()))])) {
                                unset($tickets[Factory::getApplication()->getInput()->getString('bfFlashUploadTicket', '')]);
                                $this->app->getSession()->set('bfFlashUploadTickets', $tickets);
                            }
                        }
                    } // if
                } // if
            } // if
            // handle End Submit piece
            // DOUBLE OPT-IN

            if ($this->formrow->double_opt) {
                $uri = Uri::getInstance();
                $domainAddress = $uri->toString(array('scheme', 'host', 'port', 'path'));

                $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
                $config = Factory::getApplication()->getConfig();

                $recipient = '';
                $email_field_name = $this->formrow->opt_mail;

                // getting the email address from the form based on the setting in admin
                foreach ($this->submitdata as $data) {

                    if ($data[_FF_DATA_NAME] == $email_field_name) {
                        $recipient = $data[_FF_DATA_VALUE];
                        break;
                    }
                }

                if (bf_is_email($recipient)) {

                    $this->database->setQuery("Select s.record From #__facileforms_subrecords As s, #__facileforms_records As r Where r.form = " . $this->database->quote($this->form) . " And r.id = s.record And s.`name` = " . $this->database->quote($email_field_name) . " And s.`value` = " . $this->database->quote($recipient) . " And r.`opted` = 1");
                    $exists = $this->database->loadResult();

                    if (!$exists) {

                        $mailer->addRecipient($recipient);
                        $sender = array(
                            $config->get('mailfrom'),
                            $config->get('fromname')
                        );

                        $lastID = $this->record_id;
                        $token = $this->random_str(20);
                        $this->database->setQuery("UPDATE #__facileforms_records SET opt_token=" . $this->database->quote(bf_b64enc($token)) . " WHERE id=" . $this->database->quote($lastID));
                        $this->database->execute();

                        $opt_in_link = $domainAddress . '?option=com_breezingformsng&opt_in=true&id=' . $lastID . '&' . 'token=' . bf_b64enc($token);
                        $opt_out_link = $domainAddress . '?option=com_breezingformsng&opt_out=true&id=' . $lastID . '&' . 'token=' . bf_b64enc($token);

                        $message = Text::_('COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_EMAIL_TEXT');
                        $message .= '<a href="' . $opt_in_link . '">' . Text::_('COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_VERIFY_HERE') . '</a>';
                        $message .= Text::_('COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_OUT_EMAIL_TEXT');
                        $message .= '<a href="' . $opt_out_link . '">' . Text::_('COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_UNVERIFY_HERE') . '</a>';
                        $message .= Text::_('COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_EMAIL_TEXT_FOOTER');

                        $body = $message;
                        $mailer->isHtml(true);
                        $mailer->setSubject(Text::_('COM_BREEZINGFORMSNG_FORMS_DOUBLE_OPT_EMAIL_SUBJECT'));
                        $mailer->setBody($body);
                        $mailer->setSender($sender);
                        $mailer->Send();
                    }
                }
            }

            // DOUBLE OPT-INT END

            $this->database->setQuery("SELECT MAX(id) FROM #__facileforms_records");
            $lastid = $this->database->loadResult();
            $_SESSION['virtuemart_bf_id'] = $lastid;
            $session = $this->app->getSession();
            $session->set('virtuemart_bf_id', $lastid);

            $code = '';
            switch ($this->formrow->piece4cond) {
                case 1: // library
                    $this->database->setQuery(
                        "select name, code from #__facileforms_pieces " .
                        "where id=" . $this->formrow->piece4id . " and published=1 "
                    );
                    $rows = $this->database->loadObjectList();
                    if (count($rows))
                        echo $this->execPiece(
                            $rows[0]->code,
                            Text::_('COM_BREEZINGFORMSNG_PROCESS_ESPIECE') . " " . $rows[0]->name,
                            'p',
                            $this->formrow->piece4id,
                            null
                        );
                    break;
                case 2: // custom code
                    echo $this->execPiece(
                        $this->formrow->piece4code,
                        Text::_('COM_BREEZINGFORMSNG_PROCESS_ESPIECEC'),
                        'f',
                        $this->form,
                        3
                    );
                    break;
                default:
                    break;
            } // switch

            if ($this->bury())
                return;
        }

        switch ($this->status) {
            case _FF_STATUS_OK:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_SUBMITSUCCESS');
                break;
            case _FF_STATUS_UNPUBLISHED:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_UNPUBLISHED');
                break;
            case _FF_STATUS_SAVERECORD_FAILED:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_SAVERECFAILED');
                break;
            case _FF_STATUS_SAVESUBRECORD_FAILED:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_SAVESUBFAILED');
                break;
            case _FF_STATUS_UPLOAD_FAILED:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_UPLOADFAILED');
                break;
            case _FF_STATUS_SENDMAIL_FAILED:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_SENDMAILFAILED');
                break;
            case _FF_STATUS_ATTACHMENT_FAILED:
                $message = Text::_('COM_BREEZINGFORMSNG_PROCESS_ATTACHMTFAILED');
                break;
            case _FF_STATUS_CAPTCHA_FAILED:
                $message = Text::_('COM_BREEZINGFORMSNG_CAPTCHA_ENTRY_FAILED');
                break;
            case _FF_STATUS_FILE_EXTENSION_NOT_ALLOWED:
                $message = Text::_('COM_BREEZINGFORMSNG_FILE_EXTENSION_NOT_ALLOWED');
                break;
            default:
                $message = '';
                // custom piece status and message
                break;
        } // switch
        // built in PayPal action
        $paymentAction = false;

        if ($this->formrow->template_code != '') {


            $areas = json_decode($this->formrow->template_areas, true);
            $head = json_decode(bf_b64dec($this->formrow->template_code), true);

            if (is_array($areas)) {
                $j15 = false;


                $paymentAction = true;

                switch (Factory::getApplication()->getInput()->getString('ff_payment_method', '')) {


                    case 'Stripe':

                        foreach ($area['elements'] as $element) {

                            if ($element['internalType'] == 'bfStripe') {

                                $options = $element['options'];

                                $ppselect = Factory::getApplication()->getInput()->get('ff_nm_bfPaymentSelect', [], 'string');
                                if (count($ppselect) != 0) {
                                    $ppselected = explode('|', $ppselect[0]);
                                    if (count($ppselected) == 4) {
                                        // XDA - BUG : useless with normal itemname
                                        // Replace item name for Stripe button field with TEXT1 TEXT2 values from bfPaymentSelect text build as TEXT1|TEXT2|MNT1|MNT2
                                        // $options['itemname'] = $ppselected[0] . ' ' . $ppselected[1];
                                        $options['amount'] = floatval($ppselected[2]) + floatval($ppselected[3]);
                                    }
                                }

                                $options['amount'] = round(floatval($options['amount']), 2) * 100;

                                $this->app->getSession()->set('bf_stripe_last_payment_amount' . $this->record_id, $options['amount']);

                                $html = '';

                                if (!$this->inline)
                                    /*$html .= '<html><head><style> .stripe_checkout_app { height: 580px !important; } </style>
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">
.thebutton {
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
-ms-box-sizing: border-box;
-o-box-sizing: border-box;
box-sizing: border-box;
padding: 0;
margin: 0;
width: 120px;
height: 37px;
border: 0;
text-decoration: none;
background: #45b1e8;
cursor: pointer;
background-image: -webkit-linear-gradient(#45b1e8,#3097de);
background-image: -moz-linear-gradient(#45b1e8,#3097de);
background-image: -ms-linear-gradient(#45b1e8,#3097de);
background-image: -o-linear-gradient(#45b1e8,#3097de);
background-image: -webkit-linear-gradient(#45b1e8,#3097de);
background-image: -moz-linear-gradient(#45b1e8,#3097de);
background-image: -ms-linear-gradient(#45b1e8,#3097de);
background-image: -o-linear-gradient(#45b1e8,#3097de);
background-image: linear-gradient(#45b1e8,#3097de);
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
-ms-border-radius: 4px;
-o-border-radius: 4px;
border-radius: 4px;
-webkit-font-smoothing: antialiased;
-webkit-touch-callout: none;
-webkit-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
-o-user-select: none;
user-select: none;
cursor: pointer;
font-family: "Helvetica Neue","Helvetica",Arial,sans-serif;
font-weight: bold;
font-size: 17px;
color: #fff;
text-shadow: 0 -1px 0 rgba(46,86,153,0.3);
-webkit-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-moz-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-ms-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-o-box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
box-shadow: 0 1px 0 rgba(46,86,153,0.15),0 0 4px rgba(86,149,219,0),inset 0 2px 0 rgba(41,102,20,0);
-webkit-transition: box-shadow .15s linear;
-moz-transition: box-shadow .15s linear;
-ms-transition: box-shadow .15s linear;
-o-transition: box-shadow .15s linear;
transition: box-shadow .15s linear;
}
</style></head><body>';*/
                                    \Vcmb\Component\BreezingformsNG\Administrator\Helper\VendorHelper::load();
                                \Stripe\Stripe::setApiKey($options['secretKey']);
                                $stripeemail = strtolower((Factory::getApplication()->getInput()->get('ff_nm_' . $options['emailfield'], '', 'string')[0] ?? ''));
                                //header('Content-Type: application/json');
                                $returnurl = Uri::root() . "index.php?option=com_breezingformsng&confirmStripe=true&form_id=" . $this->form . "&record_id=" . $this->record_id;
                                if (isset($options['emailfield']) && $options['emailfield'] !== '') {
                                    $stripeemail = strtolower((Factory::getApplication()->getInput()->get('ff_nm_' . $options['emailfield'], '', 'string')[0] ?? ''));
                                    $this->app->getSession()->set('emailfield', $stripeemail);
                                }

                                // XDA BEGIN
// preg_match('#\((.*?)\)#', $_POST['ff_nm_donationAmount'][0], $match);
// $productName = $match[1];
                                $productName = $options['itemname'];
                                // XDA END

                                // XDA $pricearr = explode(" ", $$_POST['ff_nm_donationAmount'][0], 2);
// XDA $productPrice = $pricearr[0];

                                // ---------------------------------------------------------------------------------------------------------------------------------------------
// XDA : in the Stripe Checkout session, 2 changes :
// 1 - To disable address collection we will either need to pass billing_address_collection parameter with value auto or send API request without it.
//      billing_address_collection' => 'required' -> 'auto'
// 2 - Email determination
// 'customer_email' => $_POST['ff_nm_email'][0] -> 'customer_email' => $stripeemail
// ---------------------------------------------------------------------------------------------------------------------------------------------


                                $checkout_session = \Stripe\Checkout\Session::create([
                                    'customer_email' => $stripeemail,
                                    'billing_address_collection' => 'auto',
                                    /*
                                    'line_items' => [[
                                      'price' => 'price_1JYA3UDkYxK6vMJ2QF2S6fNh',
                                      'quantity' => 1,
                                      //'description' => var_dump($stripeemail);
                                    ]],*/
                                    'line_items' => [
                                        [
                                            'price_data' => [
                                                'currency' => strtolower($options['currencyCode']),
                                                'product_data' => [
                                                    'name' => "$productName",
                                                ],
                                                'unit_amount' => $options['amount'],
                                            ],
                                            'quantity' => 1,
                                        ]
                                    ],
                                    'payment_method_types' => [
                                        'card',
                                    ],
                                    'mode' => 'payment',
                                    'success_url' => $returnurl . "&session_id={CHECKOUT_SESSION_ID}",
                                    'cancel_url' => $returnurl . "&session_id={CHECKOUT_SESSION_ID}",
                                ]);

                                //$html .=  $_POST['ff_nm_email'][0];
                                $html .= header("HTTP/1.1 303 See Other");
                                $html .= header("Location: " . $checkout_session->url);

                                $current_tag = $this->app->getLanguage()->getTag();
                                $exploded = explode('-', $current_tag);

                                $locale = 'auto';

                                if (in_array(strtolower($exploded[0]), array('zh', 'nl', 'en', 'fr', 'de', 'it', 'ja', 'es'))) {

                                    $locale = strtolower($exploded[0]);
                                }

                                $returnurl = Uri::root() . "index.php?option=com_breezingformsng&confirmStripe=true&form_id=" . $this->form . "&record_id=" . $this->record_id;
                                if (isset($options['emailfield']) && $options['emailfield'] !== '') {
                                    $stripeemail = strtolower((Factory::getApplication()->getInput()->get('ff_nm_' . $options['emailfield'], '', 'string')[0] ?? ''));
                                    $this->app->getSession()->set('emailfield', $stripeemail);
                                }

                                $html .= '
                        
                        <script src="https://checkout.stripe.com/checkout.js"></script>
        
        <script>
        var submitted_form = false;
        
        var handler = StripeCheckout.configure({
            key: ' . json_encode($options['publishableKey']) . ',
            image: ' . json_encode(Uri::root() . 'media/com_breezingformsng/images/site/icon_card.png') . ',
            locale: ' . json_encode($locale) . ',
            token: function(token) {
            submitted_form = true;
            location.href = ' . json_encode($returnurl) . '+"&token="+token.id
            }
                                                                });
                            
                            var options = {
                                        name: ' . json_encode(isset($head['properties']['title_translation' . $this->app->getLanguage()->getTag()]) ? $head['properties']['title_translation' . $this->app->getLanguage()->getTag()] : $this->formrow->title) . ',
                                        description: ' . json_encode($options['itemname']) . ',
                                        currency: ' . json_encode(strtolower($options['currencyCode'])) . ',
                                        amount: ' . json_encode($options['amount']) . ',
                                        email: ' . (isset($stripeemail) ? json_encode($stripeemail) : json_encode('')) . ',
									    zipCode : true,
									    billingAddress: true,
									    closed: function () { 
									        if( !submitted_form ){
									        
									            location.href = ' . json_encode(Uri::root()) . '; 
                                                                                }
                                                                            },
                                                                            opened: function(){
                                                                                document.querySelector(".thebutton").style.display = "none";
                                                                            }
                                                                  };
                                                                
								// Close Checkout on page navigation:
								window.addEventListener(\'popstate\', function() {
								  handler.close();
								});
								
								window.onload = function(){
								  handler.open(options);
								};
								</script>
			                	
			                	';

                                if (!$this->inline)
                                    $html .= "</form><div style='margin-top: 25%; text-align: center; width: 100%;'><button class='thebutton' onclick='handler.open(options);'>Click To Pay</button></body></div></html>";

                                echo $html;
                            }
                        }

                        break;

                    case 'PayPal':

                        foreach ($areas as $area) {

                            foreach ($area['elements'] as $element) {

                                if ($element['internalType'] == 'bfPayPal') {

                                    $options = $element['options'];

                                    $business = $options['business'];
                                    $paypal = 'https://www.paypal.com';

                                    if ($options['testaccount']) {
                                        $paypal = 'https://www.sandbox.paypal.com';
                                        $business = $options['testBusiness'];
                                    }

                                    $returnurl = htmlentities(Uri::root() . "index.php?option=com_breezingformsng&confirmPayPal=true&form_id=" . $this->form . "&record_id=" . $this->record_id);
                                    // $cancelurl = htmlentities(Uri::root() . "index.php?msg=" . Text::_('Transaction Cancelled'));
                                    $cancelurl = $options['cancelURL'];

                                    $html = '';
                                    if (!$this->inline)
                                        $html .= '<html><head><meta charset="UTF-8"></head><body>';

                                    HTMLHelper::_('bootstrap.modal');

                                    $ppselect = Factory::getApplication()->getInput()->get('ff_nm_bfPaymentSelect', [], 'string');
                                    if (count($ppselect) != 0) {
                                        $ppselected = explode('|', $ppselect[0]);
                                        if (count($ppselected) == 4) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 5) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 6) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                            $options['currencyCode'] = htmlentities($ppselected[5] == '' ? 'USD' : $ppselected[5], ENT_QUOTES, 'UTF-8');
                                        }
                                    }

                                    // keeping this for compat reasons
                                    $ppselect = Factory::getApplication()->getInput()->get('ff_nm_PayPalSelect', [], 'string');
                                    if (count($ppselect) != 0) {
                                        $ppselected = explode('|', $ppselect[0]);
                                        if (count($ppselected) == 4) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 5) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                        } else if (count($ppselected) == 6) {
                                            $options['itemname'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['itemnumber'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            $options['tax'] = htmlentities($ppselected[3], ENT_QUOTES, 'UTF-8');
                                            $options['shipping'] = htmlentities($ppselected[4] == 1 ? 1 : 0, ENT_QUOTES, 'UTF-8');
                                            $options['currencyCode'] = htmlentities($ppselected[5] == '' ? 'USD' : $ppselected[5], ENT_QUOTES, 'UTF-8');
                                        }
                                    }
                                    // compat end

                                    $html .= "<form name=\"ff_submitform\" action=\"" . $paypal . "/cgi-bin/webscr\" method=\"post\" accept-charset=\"UTF-8\">";
                                    $html .= "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"/>";
                                    $html .= "<input type=\"hidden\" name=\"business\" value=\"" . $business . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $options['itemname'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $options['itemnumber'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"amount\" value=\"" . $options['amount'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"tax\" value=\"" . $options['tax'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"no_shipping\" value=\"" . ($options['shipping'] == 1 ? 0 : 1) . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"no_note\" value=\"1\"/>";
                                    if ($options['useIpn']) {
                                        $html .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . htmlentities(Uri::root() . "index.php?option=com_breezingformsng&confirmPayPalIpn=true&raw=true&form_id=" . $this->form . "&record_id=" . $this->record_id) . "\"/>";
                                        if ($options['testaccount']) {
                                            $html .= "<input type=\"hidden\" name=\"test_ipn\" value=\"1\"/>";
                                        }

                                        $html .= "<input type=\"hidden\" name=\"return\" value=\"" . $returnurl . "\"/>";
                                    } else {

                                        $html .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $returnurl . "\"/>";
                                        $html .= "<input type=\"hidden\" name=\"return\" value=\"" . $returnurl . "\"/>";
                                    }

                                    $html .= "<input type=\"hidden\" name=\"cancel_return\" value=\"" . $cancelurl . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"rm\" value=\"2\"/>";
                                    $html .= "<input type=\"hidden\" name=\"lc\" value=\"" . $options['locale'] . "\"/>";
                                    $html .= "<input type=\"hidden\" name=\"currency_code\" value=\"" . strtoupper($options['currencyCode']) . "\"/>";

                                    if (!$this->inline)
                                        $html .= "</form></body></html>";

                                    // TODO: let the user decide to use modal or simple alert
                                    if ($j15) {
                                        $html .= '<script type="text/javascript">' . nl() .
                                            indentc(1) . '<!--' . nl() .
                                            indentc(2) . '

										    SqueezeBox.initialize({});

										    SqueezeBox.loadModal = function(modalUrl,handler,x,y) {
										    		this.initialize();
										      		var options = $merge(options || {}, Json.evaluate("{handler: \'" + handler + "\', size: {x: " + x +", y: " + y + "}}"));
													this.setOptions(this.presets, options);
													this.assignOptions();
													this.setContent(handler,modalUrl);
										   	};

										    SqueezeBox.loadModal("' . Uri::root() . 'index.php?raw=true&option=com_breezingformsng&showPayPalConnectMsg=true","iframe",300,100);

										 	

										' . nl() .
                                            indentc(1) . '// -->' . nl() .
                                            '</script>' . nl();
                                    }
                                    $html .= '<script type="text/javascript"><!--' . nl() . 'document.ff_submitform.submit();' . nl() . '//--></script>';
                                    echo $html;

                                    break;
                                }
                            }
                        }

                        break;

                    case 'Sofortueberweisung':

                        foreach ($areas as $area) {
                            foreach ($area['elements'] as $element) {
                                if ($element['internalType'] == 'bfSofortueberweisung') {

                                    $html = '';
                                    if (!$this->inline)
                                        $html .= '<html><head></head><body>';

                                    HTMLHelper::_('bootstrap.modal');

                                    $options = $element['options'];

                                    $ppselect = Factory::getApplication()->getInput()->get('ff_nm_bfPaymentSelect', [], 'string');
                                    if (count($ppselect) != 0) {
                                        $ppselected = explode('|', $ppselect[0]);
                                        if (count($ppselected) == 4) {
                                            $options['reason_1'] = htmlentities($ppselected[0], ENT_QUOTES, 'UTF-8');
                                            $options['reason_2'] = htmlentities($ppselected[1], ENT_QUOTES, 'UTF-8');
                                            $options['amount'] = htmlentities($ppselected[2], ENT_QUOTES, 'UTF-8');
                                            if ($ppselected[3] != '' && intval($ppselected[3]) > 0) {
                                                $options['amount'] = '' . (doubleval($options['amount']) + doubleval($ppselected[3]));
                                            }
                                        }
                                    }

                                    $options['amount'] = str_replace('.', ',', $options['amount']);

                                    $hash = '';
                                    if (isset($options['project_password']) && trim($options['project_password']) != '') {

                                        $data = array(
                                            $options['user_id'], // user_id
                                            $options['project_id'], // project_id
                                            '', // sender_holder
                                            '', // sender_account_number
                                            '', // sender_bank_code
                                            '', // sender_country_id
                                            $options['amount'], // amount
                                            // currency_id, Pflichtparameter bei Hash-Berechnung
                                            $options['currency_id'],
                                            $options['reason_1'], // reason_1
                                            $options['reason_2'], // reason_2
                                            $this->form, // user_variable_0
                                            $this->record_id, // user_variable_1
                                            (isset($options['mailback']) && $options['mailback'] ? implode('###', $this->mailbackRecipients) : ''), // user_variable_2
                                            '', // user_variable_3
                                            '', // user_variable_4
                                            '', // user_variable_5
                                            $options['project_password']    // project_password
                                        );
                                        $data_implode = implode('|', $data);

                                        $gen = sha1($data_implode);

                                        $hash = '<input type="hidden" name="hash" value="' . $gen . '" />';
                                    }

                                    $mailback = '';
                                    if (isset($options['mailback']) && $options['mailback']) {
                                        $mailback = '<input type="hidden" name="user_variable_2" value="' . implode('###', $this->mailbackRecipients) . '" />';
                                    }

                                    $html .= '
									<!-- sofortüberweisung.de -->
									<form method="post" name="ff_submitform" action="https://www.sofortueberweisung.de/payment/start">
									<input type="hidden" name="user_id" value="' . $options['user_id'] . '" />
									<input type="hidden" name="project_id" value="' . $options['project_id'] . '" />
									<input type="hidden" name="reason_1" value="' . $options['reason_1'] . '" />
									<input type="hidden" name="reason_2" value="' . $options['reason_2'] . '" />
									<input type="hidden" name="amount" value="' . $options['amount'] . '" />
									<input type="hidden" name="currency_id" value="' . $options['currency_id'] . '" />
									<input type="hidden" name="language_id" value="' . $options['language_id'] . '" />
									<input type="hidden" name="user_variable_0" value="' . $this->form . '" />
									<input type="hidden" name="user_variable_1" value="' . $this->record_id . '" />
									' . $mailback . '
									' . $hash . '
									</form>
									<!-- sofortüberweisung.de -->
									';

                                    if ($j15) {
                                        // TODO: let the user decide to use modal or simple alert
                                        $html .= '<script type="text/javascript">' . nl() .
                                            indentc(1) . '<!--' . nl() .
                                            indentc(2) . '

										    SqueezeBox.initialize({});

										    SqueezeBox.loadModal = function(modalUrl,handler,x,y) {
										    		this.initialize();
										      		var options = $merge(options || {}, Json.evaluate("{handler: \'" + handler + "\', size: {x: " + x +", y: " + y + "}}"));
													this.setOptions(this.presets, options);
													this.assignOptions();
													this.setContent(handler,modalUrl);
										   	};

										    SqueezeBox.loadModal("' . Uri::root() . 'index.php?raw=true&option=com_breezingformsng&showPayPalConnectMsg=true","iframe",300,100);

										' . nl() .
                                            indentc(1) . '// -->' . nl() .
                                            '</script>' . nl();
                                    }
                                    $html .= '<script type="text/javascript"><!--' . nl() . 'document.ff_submitform.submit();' . nl() . '//--></script>';

                                    if (!$this->inline)
                                        $html .= "</form></body></html>";

                                    echo $html;

                                    break;
                                }
                            }
                        }

                        break;

                    default:
                        $paymentAction = false;
                }
            }
        }

        // CONTENTBUILDER
        if (Factory::getApplication()->getInput()->get('cb_controller', null, 'string') != 'edit' && $cbRecordId && is_array($cbResult) && isset($cbResult['data']) && isset($cbResult['data']['id']) && $cbResult['data']['id']) {
            if ($cbRecordId) {
                $return = Factory::getApplication()->getInput()->getString('return', '');
                if ($return) {
                    $return = bf_b64dec($return);
                    if (Uri::isInternal($return)) {
                        $this->app->redirect($return);
                    }
                }
            }

            if ($cbResult['data']['force_login']) {
                $is15 = false;

                if (!Factory::getApplication()->getIdentity()->get('id', 0)) {
                    $this->app->redirect(Route::_('index.php?option=com_users&view=login&Itemid=' . Factory::getApplication()->getInput()->getInt('Itemid', 0), false));
                } else {

                    $this->app->redirect(Route::_('index.php?option=com_users&view=profile&Itemid=' . Factory::getApplication()->getInput()->getInt('Itemid', 0), false));
                }
            } else if (trim($cbResult['data']['force_url'])) {
                $this->app->redirect(trim($cbResult['data']['force_url']));
            }

            $this->app->enqueueMessage(Text::_('COM_CONTENTBUILDERNG_SAVED'), 'success');
            $this->app->redirect(Route::_('index.php?option=com_contentbuilderng&task=details.display&Itemid=' . Factory::getApplication()->getInput()->getInt('Itemid', 0) . '&backtolist=' . Factory::getApplication()->getInput()->getInt('backtolist', 0) . '&id=' . $cbResult['data']['id'] . '&record_id=' . $cbRecordId . '&limitstart=' . Factory::getApplication()->getInput()->getInt('limitstart', 0) . '&filter_order=' . Factory::getApplication()->getInput()->getCmd('filter_order', ''), false));
        }

        if (!$paymentAction) {

            if (defined('CRBCBF_INLINE')) {

                return;
            }

            if (!defined('VMBFCF_RUNNING')) {
                $ob = 0;
                while (@ob_get_level() > 0 && $ob <= 32) {
                    @ob_end_clean();
                    $ob++;
                }
                ob_start();
                echo '<!DOCTYPE html>
                    <html>
                    <head></head>
                    <body>';
            }

            if ($message == '')
                $message = $this->message;
            else {
                if ($this->message != '')
                    $message .= ":" . nl() . $this->message;
            } // if

            if (!$this->inline) {
                $url = ($this->inframe) ? $ff_mossite . '/index.php?format=html&tmpl=component' : (($this->runmode == _FF_RUNMODE_FRONTEND) ? '' : 'index.php?format=html' . (Factory::getApplication()->getInput()->getCmd('tmpl', '') ? '&tmpl=' . Factory::getApplication()->getInput()->getCmd('tmpl', '') : ''));
                echo '<form name="ff_submitform" action="' . $url . '" method="post">' . nl();
            } // if

            switch ($this->runmode) {
                case _FF_RUNMODE_FRONTEND:
                    echo indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->target > 1)
                        echo indentc(1) . '<input type="hidden" name="ff_target" value="' . htmlentities($this->target, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->inframe)
                        echo indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl();
                    if ($this->border)
                        echo indentc(1) . '<input type="hidden" name="ff_border" value="1"/>' . nl();
                    if ($this->page != 1)
                        indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->align != 1)
                        echo indentc(1) . '<input type="hidden" name="ff_align" value="' . htmlentities($this->align, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->top != 0)
                        echo indentc(1) . '<input type="hidden" name="ff_top" value="' . htmlentities($this->top, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    reset($ff_otherparams);
                    foreach ($ff_otherparams as $prop => $val)
                        echo indentc(1) . '<input type="hidden" name="' . htmlentities($prop, ENT_QUOTES, 'UTF-8') . '" value="' . htmlentities($val, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    break;

                case _FF_RUNMODE_BACKEND:
                    echo indentc(1) . '<input type="hidden" name="option" value="com_breezingformsng"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="act" value="run"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                        indentc(1) . '<input type="hidden" name="ff_runmode" value="' . htmlentities($this->runmode, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->target > 1)
                        echo indentc(1) . '<input type="hidden" name="ff_target" value="' . htmlentities($this->target, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->inframe)
                        echo indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl();
                    if ($this->border)
                        echo indentc(1) . '<input type="hidden" name="ff_border" value="1"/>' . nl();
                    if ($this->page != 1)
                        indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->align != 1)
                        echo indentc(1) . '<input type="hidden" name="ff_align" value="' . htmlentities($this->align, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    if ($this->top != 0)
                        echo indentc(1) . '<input type="hidden" name="ff_top" value="' . htmlentities($this->top, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    break;

                default: // _FF_RUNMODE_PREVIEW:
                    if ($this->inframe) {
                        echo indentc(1) . '<input type="hidden" name="option" value="com_breezingformsng"/>' . nl() .
                            indentc(1) . '<input type="hidden" name="ff_frame" value="1"/>' . nl() .
                            indentc(1) . '<input type="hidden" name="ff_form" value="' . htmlentities($this->form, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                            indentc(1) . '<input type="hidden" name="ff_runmode" value="' . htmlentities($this->runmode, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                        if ($this->page != 1)
                            indentc(1) . '<input type="hidden" name="ff_page" value="' . htmlentities($this->page, ENT_QUOTES, 'UTF-8') . '"/>' . nl();
                    } // if
            } // if

            echo indentc(1) . '<input type="hidden" name="ff_contentid" value="' . Factory::getApplication()->getInput()->getInt('ff_contentid', 0) . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_applic" value="' . Factory::getApplication()->getInput()->getWord('ff_applic', '') . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_record_id" value="' . $this->record_id . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_module_id" value="' . Factory::getApplication()->getInput()->getInt('ff_module_id', 0) . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_status" value="' . htmlentities($this->status, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_message" value="' . htmlentities($message, ENT_QUOTES, 'UTF-8') . '"/>' . nl() .
                indentc(1) . '<input type="hidden" name="ff_form_submitted" value="1"/>' . nl();

            if (Factory::getApplication()->getInput()->getString('tmpl', '') == 'component') {
                echo indentc(1) . '<input type="hidden" name="tmpl" value="component"/>' . nl();
            }
            if (Factory::getApplication()->getInput()->getInt('cb_form_id', 0)) {
                echo indentc(1) . '<input type="hidden" name="cb_form_id" value="' . Factory::getApplication()->getInput()->getInt('cb_form_id', 0) . '"/>' . nl();
                if (Factory::getApplication()->getInput()->getInt('cb_record_id', 0)) {
                    echo indentc(1) . '<input type="hidden" name="cb_record_id" value="' . Factory::getApplication()->getInput()->getInt('cb_record_id', 0) . '"/>' . nl();
                }
                if (Factory::getApplication()->getInput()->getBool('cbIsNew', false)) {
                    echo indentc(1) . '<input type="hidden" name="cbIsNew" value="1"/>' . nl();
                }
            }
            if (Factory::getApplication()->getInput()->getString('return', '') !== '') {
                echo indentc(1) . '<input type="hidden" name="return" value="' . htmlentities(Factory::getApplication()->getInput()->getString('return', ''), ENT_QUOTES, 'UTF-8') . '"/>' . nl();
            }
            // TODO: turn off tracing in the options
            if ($this->traceMode & _FF_TRACEMODE_DIRECT) {
                $this->dumpTrace();
                ob_end_flush();
                echo '</pre>';
            } else {

                ob_end_flush();
                $this->dumpTrace();
            } // if
            restore_error_handler();

            if (!$this->inline) {
                echo '</form>' . nl() .
                    '<script type="text/javascript">' . nl() .
                    indentc(1) . '<!--' . nl() .
                    indentc(2) . 'document.ff_submitform.submit();' . nl() .
                    indentc(1) . '// -->' . nl() .
                    '</script>' . nl();
            } // if

            if (!defined('VMBFCF_RUNNING')) {
                $c = @ob_get_contents();
                @ob_end_clean();
                echo $c;

                echo '</body>
                      </html>';
            }
        }

        unset($_SESSION['ff_editable_overridePlg' . Factory::getApplication()->getInput()->getInt('ff_contentid', 0) . $this->form_id]);
        unset($_SESSION['ff_editablePlg' . Factory::getApplication()->getInput()->getInt('ff_contentid', 0) . $this->form_id]);
        $this->app->getSession()->set('ff_editableMod' . Factory::getApplication()->getInput()->getInt('ff_module_id', 0) . $this->form_id, 0);
        $this->app->getSession()->set('ff_editable_overrideMod' . Factory::getApplication()->getInput()->getInt('ff_module_id', 0) . $this->form_id, 0);

        if (!defined('VMBFCF_RUNNING')) {
            exit;
        }
    }

    private function getEvent(string $name): EventInterface
    {

        return new Event($name);
    }

    function removeDangerousHtml($value)
    {

        if (trim($value) == '')
            return '';

        $doc = new DOMDocument();
        $ret = @$doc->loadHTML('<div>' . $value . '</div>');

        if ($ret) {

            $script_tags = $doc->getElementsByTagName('script');
            $length = $script_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $script_tags->item($ii)->parentNode->removeChild($script_tags->item($ii));
            }
            $style_tags = $doc->getElementsByTagName('style');
            $length = $style_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $style_tags->item($ii)->parentNode->removeChild($style_tags->item($ii));
            }
            $iframe_tags = $doc->getElementsByTagName('iframe');
            $length = $iframe_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $iframe_tags->item($ii)->parentNode->removeChild($iframe_tags->item($ii));
            }
            $applet_tags = $doc->getElementsByTagName('applet');
            $length = $applet_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $applet_tags->item($ii)->parentNode->removeChild($applet_tags->item($ii));
            }
            $link_tags = $doc->getElementsByTagName('link');
            $length = $link_tags->length;
            for ($ii = 0; $ii < $length; $ii++) {
                $link_tags->item($ii)->parentNode->removeChild($link_tags->item($ii));
            }

            $mock = new DOMDocument;
            $body = $doc->getElementsByTagName('body')->item(0);
            foreach ($body->childNodes as $child) {
                $mock->appendChild($mock->importNode($child, true));
            }

            $container = $mock->getElementsByTagName('div')->item(0);

            $container = $container->parentNode->removeChild($container);

            while ($mock->firstChild) {
                $mock->removeChild($mock->firstChild);
            }

            while ($container->firstChild) {
                $mock->appendChild($container->firstChild);
            }

            return $mock->saveHTML();
        }

        return strip_tags($value);
    }

    // submit
}
