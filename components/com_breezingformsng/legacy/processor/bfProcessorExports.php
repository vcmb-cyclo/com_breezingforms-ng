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
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use CB\Component\Contentbuilderng\Administrator\Helper\ContentbuilderngHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\FormSourceFactory;
use CB\Component\Contentbuilderng\Administrator\Service\ArticleService;
use CB\Component\Contentbuilderng\Administrator\Service\ListSupportService;
use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;
use Vcmb\Component\BreezingformsNG\Administrator\Service\PdfDocument;

/**
 * Database logging, mailing primitives and PDF/CSV/XML exports.
 */
trait bfProcessorExports
{
    function logToDatabase($cbResult = null)
    { // CONTENTBUILDER
        global $ff_config;
        if ($this->dying)
            return;

        if (!is_object($cbResult['form']) && $this->editable && $this->editable_override) {
            $this->database->setQuery("Select id From #__facileforms_records Where form = " . $this->database->Quote($this->form) . " And user_id = " . $this->database->Quote(Factory::getApplication()->getIdentity()->get('id', 0)) . " And user_id <> 0");
            $records = $this->database->loadObjectList();
            foreach ($records as $record) {
                $this->database->setQuery("Delete From #__facileforms_subrecords Where record = " . $record->id);
                $this->database->execute();
                $this->database->setQuery("Delete From #__facileforms_records Where id = " . $record->id);
                $this->database->execute();
            }
        }

        $record = new facileFormsRecords($this->database);
        $record->submitted = $this->submitted;
        $record->form = $this->form;
        $record->title = $this->formrow->title;
        $record->name = $this->formrow->name;
        $record->ip = $this->ip;
        $record->browser = $this->browser;
        $record->opsys = $this->opsys;
        $record->provider = $this->provider;
        $record->viewed = 0;
        $record->exported = 0;
        $record->archived = 0;
        if (Factory::getApplication()->getIdentity()->get('id', 0) > 0) {
            $record->user_id = Factory::getApplication()->getIdentity()->get('id', 0);
            $record->username = Factory::getApplication()->getIdentity()->get('username', '');
            $record->user_full_name = Factory::getApplication()->getIdentity()->get('name', '');
        } else {
            $record->user_id = Factory::getApplication()->getIdentity()->get('id', 0);
            $record->username = '-';
            $record->user_full_name = '-';
        }
        // CONTENTBUILDER WILL TAKE OVER SAVING/UPDATE IF EXISTS
        $cbFileFields = array();
        if (!is_object($cbResult['form'])) {
            if (!$record->store()) {
                $this->status = _FF_STATUS_SAVERECORD_FAILED;
                $this->message = $record->getError();
                return;
            } // if

            $record_return = $record->id;

            if ($record_return && file_exists(JPATH_ADMINISTRATOR . '/components/com_contentbuilderng/com_contentbuilderng.xml')) {
                $last_update = new \Joomla\CMS\Date\Date();
                $last_update = $last_update->toSql();
                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery("Select id From #__contentbuilderng_records Where `type` = 'com_breezingformsng' And `reference_id` = " . $db->Quote($this->form) . " And record_id = " . $db->Quote($record_return));
                $res = $db->loadResult();
                if (!$res) {
                    $db->setQuery("Insert Into #__contentbuilderng_records (session_id,`type`,last_update, published, record_id, reference_id) Values ('" . $this->app->getSession()->getId() . "','com_breezingformsng'," . $db->Quote($last_update) . ",0, " . $db->Quote($record_return) . ", " . $db->Quote($this->form) . ")");
                    $db->execute();
                } else {
                    $db->setQuery("Update #__contentbuilderng_records Set last_update = " . $db->Quote($last_update) . ",edited = edited + 1 Where `type` = 'com_breezingformsng' And `reference_id` = " . $db->Quote($this->form) . " And record_id = " . $db->Quote($record_return));
                    $db->execute();
                }
            }
        }

        $this->record_id = $record->id;

        $names = array();
        $subrecord = new facileFormsSubrecords($this->database);
        $subrecord->record = $record->id;
        if (count($this->savedata)) {

            $cbData = array();

            // CONTENTBUILDER file deletion/upgrade
            if (is_object($cbResult['form'])) {

                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery('Select SQL_CALC_FOUND_ROWS * From #__contentbuilderng_forms Where id = ' . BFRequest::getInt('cb_form_id', 0) . ' And published = 1');
                $_settings = $db->loadObject();

                $_record = $cbResult['form']->getRecord(BFRequest::getInt('record_id', 0), $_settings->published_only, $cbResult['frontend'] ? ($_settings->own_only_fe ? Factory::getApplication()->getIdentity()->get('id', 0) : -1) : ($_settings->own_only ? Factory::getApplication()->getIdentity()->get('id', 0) : -1), true);
                foreach ($_record as $_rec) {
                    $_files_deleted = array();
                    if ($_rec->recType == 'File Upload') {
                        $_array = BFRequest::getVar('cb_delete_' . $_rec->recElementId, array(), '', 'ARRAY');
                        foreach ($_array as $_key => $_arr) {
                            if ($_arr == 1) {
                                $_values = explode("\n", $_rec->recValue);
                                if (isset($_values[$_key])) {
                                    if (strpos(strtolower($_values[$_key]), '{cbsite}') === 0) {
                                        $_values[$_key] = str_replace(array('{cbsite}', '{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_values[$_key]);
                                    }
                                    if (file_exists($_values[$_key])) {
                                        File::delete($_values[$_key]);
                                    }
                                    if (!isset($_files_deleted[$_rec->recElementId])) {
                                        $_files_deleted[$_rec->recElementId] = array();
                                    }
                                    $_files_deleted[$_rec->recElementId][] = $_key;
                                }
                            }
                        }

                        if (isset($_files_deleted[$_rec->recElementId]) && is_array($_files_deleted[$_rec->recElementId]) && count($_files_deleted[$_rec->recElementId])) {
                            $_i = 0;
                            foreach ($this->savedata as $data) {
                                if ($data[_FF_DATA_ID] == $_rec->recElementId) {
                                    $_is_values = explode("\n", $_rec->recValue);
                                    $_j = 0;
                                    foreach ($_is_values as $_is_value) {
                                        if (!in_array($_j, $_files_deleted[$_rec->recElementId])) {
                                            $this->savedata[$_i][_FF_DATA_VALUE] .= $_is_value . "\n";
                                        }
                                        $_j++;
                                    }
                                    $this->savedata[$_i][_FF_DATA_VALUE] = rtrim($this->savedata[$_i][_FF_DATA_VALUE]);
                                    break;
                                }
                                $_i++;
                            }
                        } else {
                            if (true) {
                                $next = count($this->savedata);
                                $this->savedata[$next] = array();
                                $this->savedata[$next][_FF_DATA_ID] = $_rec->recElementId;
                                $this->savedata[$next][_FF_DATA_NAME] = $_rec->recName;
                                $this->savedata[$next][_FF_DATA_TITLE] = strip_tags($_rec->recTitle);
                                $this->savedata[$next][_FF_DATA_TYPE] = $_rec->recType;
                                $this->savedata[$next][_FF_DATA_VALUE] = '';
                                $_is_values = explode("\n", $_rec->recValue);
                                foreach ($_is_values as $_is_value) {
                                    $this->savedata[$next][_FF_DATA_VALUE] .= $_is_value . "\n";
                                }
                                $this->savedata[$next][_FF_DATA_VALUE] = rtrim($this->savedata[$next][_FF_DATA_VALUE]);
                            }
                        }
                    }
                }
            }
            $_savedata = array();
            if (!is_object($cbResult['form'])) {
                foreach ($this->savedata as $data) {
                    if ($data[_FF_DATA_TYPE] == 'File Upload') {
                        if (!isset($_savedata[$data[_FF_DATA_ID]])) {
                            $_savedata[$data[_FF_DATA_ID]] = '';
                        }
                        $_savedata[$data[_FF_DATA_ID]] .= $data[_FF_DATA_VALUE] . "\n";
                    }
                }
            }
            $isset = array();
            foreach ($this->savedata as $data) {
                // CONTENTBUILDER WILL TAKE OVER SAVING/UPDATE IF EXISTS
                if (!is_object($cbResult['form'])) {
                    $subrecord->id = NULL;
                    $subrecord->element = $data[_FF_DATA_ID];
                    $subrecord->name = $data[_FF_DATA_NAME];
                    $subrecord->title = strip_tags($data[_FF_DATA_TITLE]);
                    $subrecord->type = $data[_FF_DATA_TYPE];
                    if (isset($_savedata[$data[_FF_DATA_ID]]) && !isset($isset[$data[_FF_DATA_ID]])) {
                        $subrecord->value = trim($_savedata[$data[_FF_DATA_ID]]);
                    } else {
                        $subrecord->value = $data[_FF_DATA_VALUE];
                    }
                    if (!isset($isset[$data[_FF_DATA_ID]])) {
                        if (!$subrecord->store()) {
                            $this->status = _FF_STATUS_SAVESUBRECORD_FAILED;
                            $this->message = $subrecord->getError();
                            return;
                        }
                    }
                    if ($data[_FF_DATA_TYPE] == 'File Upload') {
                        $isset[$data[_FF_DATA_ID]] = true;
                    }
                } else {

                    $cbNonEditableFields = (new ListSupportService())->getListNonEditableElements($cbResult['data']['id']);

                    if (!in_array($data[_FF_DATA_ID], $cbNonEditableFields)) {

                        switch ($data[_FF_DATA_TYPE]) {
                            case 'Checkbox':
                            case 'Checkbox Group':
                            case 'Radio Button':
                            case 'Radio Group':
                            case 'Select List':
                                if (!isset($cbData[$data[_FF_DATA_ID]])) {
                                    $cbData[$data[_FF_DATA_ID]] = array();
                                }
                                $cbData[$data[_FF_DATA_ID]][] = $data[_FF_DATA_VALUE];
                                break;
                            case 'File Upload':
                                if (!isset($cbData[$data[_FF_DATA_ID]])) {
                                    $cbData[$data[_FF_DATA_ID]] = '';
                                    $cbFileFields[] = $data[_FF_DATA_ID];
                                }
                                $cbData[$data[_FF_DATA_ID]] .= $data[_FF_DATA_VALUE] . "\n";
                                break;
                            default:
                                $cbData[$data[_FF_DATA_ID]] = $data[_FF_DATA_VALUE];
                        }
                    }
                }
            } // foreach
            // CONTENTBUILDER BEGIN
            if (is_object($cbResult['form'])) {

                PluginHelper::importPlugin('contentbuilderng_submit');

                $is15 = false;

                $values = array();
                $names = $cbResult['form']->getAllElements();

                foreach ($names as $id => $name) {
                    if (isset($cbData[$id])) {
                        if (in_array($id, $cbFileFields) && trim($cbData[$id]) == '') {
                            $values[$id] = '';
                        } else if (in_array($id, $cbFileFields) && trim($cbData[$id]) != '') {
                            $values[$id] = trim($cbData[$id]);
                        } else {
                            $values[$id] = $cbData[$id];
                        }
                    }
                }

                $dispatcher = $this->app->getDispatcher();
                $dispatcher->dispatch('onBeforeSubmit', new Joomla\Event\Event('onBeforeSubmit', array(
                    BFRequest::getInt('cb_record_id', 0),
                    $cbResult['form'],
                    $values
                )
            ));

                $record_return = $cbResult['form']->saveRecord(BFRequest::getInt('cb_record_id', 0), $values);

                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery('Select SQL_CALC_FOUND_ROWS * From #__contentbuilderng_forms Where id = ' . BFRequest::getInt('cb_form_id', 0) . ' And published = 1');
                $cbData = $db->loadObject();

                if ($record_return) {

                    $this->record_id = $record_return;

                    $sef = '';
                    $ignore_lang_code = '*';
                    if ($cbResult['data']['default_lang_code_ignore']) {

                        $db->setQuery("Select lang_code From #__languages Where published = 1 And sef = " . $db->Quote(trim(BFRequest::getCmd('lang', ''))));
                        $ignore_lang_code = $db->loadResult();
                        if (!$ignore_lang_code) {
                            $ignore_lang_code = '*';
                        }

                        $sef = trim(BFRequest::getCmd('lang', ''));
                        if ($ignore_lang_code == '*') {
                            $sef = '';
                        }
                        if ($ignore_lang_code == '*') {
                            $sef = '';
                        }
                    } else {
                        $db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $db->Quote($cbResult['data']['default_lang_code']));
                        $sef = $db->loadResult();
                    }

                    $language = $cbResult['data']['default_lang_code_ignore'] ? $ignore_lang_code : $cbResult['data']['default_lang_code'];
                    $res = $db->loadResult();
                    $last_update = new \Joomla\CMS\Date\Date();
                    $last_update = $last_update->toSql();
                    if (!$res) {

                        $is_future = 0;
                        $created_up = $created_up->toSql();
                        if (intval($cbData->default_publish_up_days) != 0) {
                            $is_future = 1;
                            $date = new \Joomla\CMS\Date\Date(strtotime('now +' . intval($cbData->default_publish_up_days) . ' days'));
                            $created_up = $date->toSql();
                        }
                        $created_down = '0000-00-00 00:00:00';
                        if (intval($cbData->default_publish_down_days) != 0) {
                            $date = new \Joomla\CMS\Date\Date(strtotime($created_up . ' +' . intval($cbData->default_publish_down_days) . ' days'));
                            $created_down = $date->toSql();
                        }

                        $db->setQuery("Insert Into #__contentbuilderng_records (session_id,`type`,last_update,is_future,lang_code, sef, published, record_id, reference_id, publish_up, publish_down) Values ('" . $this->app->getSession()->getId() . "','com_breezingformsng'," . $db->Quote($last_update) . ",$is_future, " . $db->Quote($language) . "," . $db->Quote(trim($sef)) . "," . $db->Quote($cbData->auto_publish && !$is_future ? 1 : 0) . ", " . $db->Quote($record_return) . ", " . $db->Quote($cbResult['form']->getReferenceId()) . ", " . $db->Quote($created_up) . ", " . $db->Quote($created_down) . ")");
                        $db->execute();

                    } else {

                        $db->setQuery("Update #__contentbuilderng_records Set last_update = " . $db->Quote($last_update) . ",lang_code = " . $db->Quote($language) . ", sef = " . $db->Quote(trim($sef)) . ", edited = edited + 1 Where `type` = 'com_breezingformsng' And `reference_id` = " . $db->Quote($cbResult['form']->getReferenceId()) . " And record_id = " . $db->Quote($record_return));
                        $db->execute();
                    }
                }

                $article_id = 0;

                // creating the article
                if (is_object($cbData) && $cbData->create_articles) {

                    BFRequest::setVar('cb_category_id', null);
                    BFRequest::setVar('cb_controller', null);

                    if ($this->app->isClient('site') && BFRequest::getInt('Itemid', 0)) {
                        $menu = $this->app->getMenu();
                        $item = $menu->getActive();
                        if (is_object($item)) {
                            BFRequest::setVar('cb_category_id', $item->getParams()->get('cb_category_id', null));
                            BFRequest::setVar('cb_controller', $item->getParams()->get('cb_controller', null));
                        }
                    }

                    $cbData->page_title = $cbData->use_view_name_as_title ? $cbData->name : $cbResult['form']->getPageTitle();
                    $cbData->labels = $cbResult['form']->getElementLabels();
                    $ids = array();
                    foreach ($cbData->labels as $reference_id => $label) {
                        $ids[] = $db->Quote($reference_id);
                    }
                    $cbData->labels = array();
                    if (count($ids)) {
                        $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilderng_elements Where form_id = " . BFRequest::getInt('cb_form_id', 0) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                        $rows = $db->loadAssocList();
                        $ids = array();
                        foreach ($rows as $row) {
                            $ids[] = $row['reference_id'];
                        }
                    }
                    $cbData->items = $cbResult['form']->getRecord($record_return, $cbData->published_only, $cbResult['frontend'] ? ($cbData->own_only_fe ? Factory::getApplication()->getIdentity()->get('id', 0) : -1) : ($cbData->own_only ? Factory::getApplication()->getIdentity()->get('id', 0) : -1), true);
                    if (!count($cbData->items)) {
                        throw new Exception(Text::_('COM_CONTENTBUILDERNG_RECORD_NOT_FOUND'), 404);
                    }
                    $config = array();
                    foreach ($this->savedata as $data) {
                        if ($data[_FF_DATA_NAME] == 'cb_article_category_id') {
                            $config['bf_catid'] = intval($data[_FF_DATA_VALUE]);
                            break;
                        }
                    }
                    $full = false;
                    $article_id = (new ArticleService())->createArticle(BFRequest::getInt('cb_form_id', 0), $record_return, $cbData->items, $ids, $cbData->title_field, $cbResult['form']->getRecordMetadata($record_return), $config, $full, true, BFRequest::getVar('cb_category_id', null));

                    $cacheFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
                    $cache = $cacheFactory->createCacheController('callback', ['defaultgroup' => 'com_content']);
                    $cache->clean();
                    $cache = $cacheFactory->createCacheController('callback', ['defaultgroup' => 'com_contentbuilderng']);
                    $cache->clean();
                }

                $dispatcher = $this->app->getDispatcher();
                $dispatcher->dispatch('onAfterSubmit', new Joomla\Event\Event('onAfterSubmit', 
                    array(
                        $record_return,
                        $article_id,
                        $cbResult['form'],
                        $values
                    )
                ));
            }
            // CONTENTBUILDER END
        }

        require_once(JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFIntegrate.php');
        $integrate = new BFIntegrate($this->form);
        if (count($this->savedata))
            foreach ($this->savedata as $data) {
                $integrate->field($data);
            }
        $integrate->commit();

        if (isset($record_return)) {
            return $record_return;
        }
    }

    /*
     * https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
     */

    function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    // logToDatabase

    function sendMail($from, $fromname, $recipient, $subject, $body, $attachment = NULL, $html = NULL, $cc = NULL, $bcc = NULL, $alt_sender = '')
    {
        if ($this->dying)
            return;
        $mail = bf_createMail($from, $fromname, $subject, $body, $alt_sender);

        try {

            if (is_array($recipient))
                foreach ($recipient as $to)
                    $mail->AddAddress($to);
            else
                $mail->AddAddress($recipient);

            if ($attachment) {
                if (is_array($attachment)) {
                    $attCnt = count($attachment);
                    for ($i = 0; $i < $attCnt; $i++) {
                        if (trim($attachment[$i]) != '') {
                            $mail->AddAttachment($attachment[$i]);
                        }
                    }
                } else {
                    if ($attachment != '') {
                        $mail->AddAttachment($attachment);
                    }
                }
            } // if
            //***************************************************

            if (isset($html))
                $mail->IsHTML($html);

            if (isset($cc)) {
                if (is_array($cc))
                    foreach ($cc as $to)
                        $mail->AddCC($to);
                else
                    $mail->AddCC($cc);
            } // if

            if (isset($bcc)) {
                if (is_array($bcc))
                    foreach ($bcc as $to)
                        $mail->AddBCC($to);
                else
                    $mail->AddBCC($bcc);
            } // if

            if (!$mail->Send()) {
                $this->status = _FF_STATUS_SENDMAIL_FAILED;
                $this->message = $mail->ErrorInfo;
            } // if
        } catch (Exception $e) {

            $this->app->enqueueMessage($e->getMessage(), 'error');
        }
    }

    // sendMail

    function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    function exppdf($filter = array(), $mailback = false, $translate = true)
    {
        global $ff_compath;

        $tz = 'UTC';
        $tz = new DateTimeZone($this->app->get('offset'));

        $file = JPATH_SITE . '/media/breezingforms/pdftpl/' . $this->formrow->name . '_pdf_attachment.php';
        if (!file_exists($file)) {
            $file = JPATH_SITE . '/media/breezingforms/pdftpl/pdf_attachment.php';
        }

        if ($mailback) {
            $mb_file = JPATH_SITE . '/media/breezingforms/pdftpl/' . $this->formrow->name . '_pdf_mailback_attachment.php';
            if (file_exists($mb_file)) {
                $file = $mb_file;
            } else {
                $mb_file = JPATH_SITE . '/media/breezingforms/pdftpl/pdf_mailback_attachment.php';
                if (file_exists($mb_file)) {
                    $file = $mb_file;
                }
            }
        }

        $processed = array();
        $xmldata = array();

        $_xmldata = $this->xmldata;
        if ($mailback) {
            $_xmldata = $this->mb_xmldata;
        }

        foreach ($_xmldata as $data) {
            if (!in_array($data[_FF_DATA_NAME], $filter) && !in_array($data[_FF_DATA_NAME], $processed)) {
                if ($translate) {
                    $title_translated = '';
                    $this->getFieldTranslated('label', $data[_FF_DATA_NAME], $title_translated);
                    $data[_FF_DATA_TITLE] = $title_translated != '' ? $title_translated : strip_tags($data[_FF_DATA_TITLE]);
                }
                $xmldata[] = $data;
                //$processed[] = $data[_FF_DATA_NAME];
            }
        }

        $date_stamp = date('YmdHis');
        $submitted = $this->submitted;
        $date_ = new \Joomla\CMS\Date\Date($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $this->submitted = $date_->format('Y-m-d H:i:s', true);
        $date_stamp = $date_->format('YmdHis', true);

        $date_stamp2 = date('Ymd');
        $date_ = new \Joomla\CMS\Date\Date($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $date_stamp2 = $date_->format('YmdHis', true);

        $this->submitted = $submitted;

        $pdf = new PdfDocument();
        $pdf->setMailback($mailback);
        $pdf->setFormName($this->formrow->name);

        ob_start();
        require($file);
        $c = ob_get_contents();
        ob_end_clean();

        $active_found = false;
        $font_loaded = false;
        $ttf_name = '';

        if (is_dir(JPATH_SITE . '/media/breezingforms/pdftpl/fonts/')) {

            $sourcePath = JPATH_SITE . '/media/breezingforms/pdftpl/fonts/';
            if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
                while (false !== ($file = @readdir($handle))) {
                    if ($file != "." && $file != ".." && $this->endsWith(strtolower($file), '.php')) {
                        $file_sep = explode('.', $file);
                        if (count($file_sep) > 1) {
                            unset($file_sep[count($file_sep) - 1]);
                            $pdf->AddFont(implode('_', $file_sep), '', $sourcePath . $file);
                            $font_loaded = true;
                        }
                    }
                    if ($file != "." && $file != ".." && $this->endsWith(strtolower($file), '.ttf')) {
                        $file_sep = explode('.', $file);
                        if (count($file_sep) > 1) {
                            unset($file_sep[count($file_sep) - 1]);
                            $ttf_name = TCPDF_FONTS::addTTFfont($sourcePath . $file, 'TrueTypeUnicode');
                            $font_loaded = true;
                        }
                    }
                    if ($this->endsWith(strtolower($file), '_active')) {
                        $active = explode('_', $file);
                        if (count($active) > 1) {
                            unset($active[count($active) - 1]);
                            $font_name = '';
                            if ($ttf_name != '') {
                                $font_name = $ttf_name;
                            } else {
                                $font_name = implode('_', $active);
                            }
                            $pdf->SetFont($font_name);
                            if ($font_loaded) {
                                $active_found = true;
                            }
                        }
                    }
                }
                @closedir($handle);
            }
        }

        if (!$active_found) {
            TCPDF_FONTS::addTTFfont(JPATH_SITE . '/media/com_breezingformsng/fonts/verdana.ttf', 'TrueTypeUnicode');
            $pdf->SetFont('verdana');
        }

        if ($pdf->getFooterTemplate() == '') {

            $pdf->setPrintFooter(false);
        } else {

            $pdf->setPrintFooter(true);
        }

        if ($pdf->getHeaderTemplate() == '') {

            $pdf->setPrintHeader(false);
        } else {

            $pdf->setPrintHeader(true);
        }

        $pdf->AddPage();
        $pdf->writeHTML($c);
        mt_srand();

        $matches_array = array();
        $regex = '<!--(.+?)=(.+?)-->';
        preg_match_all($regex, str_replace(' ', '', $c), $matches_array);

        if (isset($matches_array[1]) && isset($matches_array[1][0]) && trim($matches_array[1][0]) == 'fm' && isset($matches_array[2]) && isset($matches_array[2][0])) {

            $fm = $matches_array[2][0];

            if (substr(trim($fm), 0, strlen('{mospath}')) === '{mospath}') {
                $fm = str_replace('{mospath}', $this->mospath, $fm);
            }

            foreach ($_xmldata as $data) {

                $value = str_replace(array('.', 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', 'ß'), array('_', 'ae', 'ue', 'oe', 'Ae', 'Ue', 'Oe', 'ss'), $data[_FF_DATA_VALUE]);

                $fm = str_replace('{' . strtolower($data[_FF_DATA_NAME]) . ':value}', File::makeSafe(trim($value)), $fm);
            }

            $fm = str_replace('{filemask:_separator}', '_', $fm);
            $fm = str_replace('{filemask:_username}', trim(Factory::getApplication()->getIdentity()->get('username')), $fm);
            $fm = str_replace('{filemask:_userid}', trim(Factory::getApplication()->getIdentity()->get('id')), $fm);
            $fm = str_replace('{filemask:_name}', trim(Factory::getApplication()->getIdentity()->get('name')), $fm);
            $fm = str_replace('{filemask:_datetime}', trim($date_stamp), $fm);
            $fm = str_replace('{filemask:_date}', trim($date_stamp2), $fm);
            $fm = str_replace('{filemask:_timestamp}', trim(time()), $fm);
            $fm = str_replace('{filemask:_random}', trim(mt_rand(0, mt_getrandmax())), $fm);
            if ($fm == '') {
                $fm = '__empty__';
            }

            $uploads = $this->uploads . '/';

            if (substr(trim($fm), 0, 1) === '/' || substr(trim($fm), 1, 1) === ':') {
                $uploads = '';
            }

            $pdfname = $uploads . $fm . '.pdf';

            if (file_exists($pdfname)) {

                $pdfname = $uploads . $fm . '-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.pdf';
            }
        } else {

            $pdfname = $this->uploads . '/ffexport-pdf-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.pdf';
        }

        $pdf->lastPage();
        $pdf->Output($pdfname, "F");
        return $pdfname;
    }

    function expcsv($filter = array(), $mailback = false)
    {
        global $ff_config;

        $inverted = isset($ff_config->csvinverted) ? $ff_config->csvinverted : false;

        $tz = 'UTC';
        $tz = new DateTimeZone($this->app->get('offset'));

        $csvdelimiter = stripslashes($ff_config->csvdelimiter);
        $csvquote = stripslashes($ff_config->csvquote);
        $cellnewline = $ff_config->cellnewline == 0 ? "\n" : "\\n";

        $fields = array();
        $lines = array();

        $lineNum = count($lines);

        $fields['ZZZ_A_FORM'] = true;
        $fields['ZZZ_B_SUBMITTED'] = true;
        $fields['ZZZ_C_IP'] = true;
        $fields['ZZZ_D_BROWSER'] = true;
        $fields['ZZZ_E_OPSYS'] = true;

        $lines[$lineNum]['ZZZ_A_FORM'][] = $this->form;

        $date_stamp = date('YmdHis');
        $submitted = $this->submitted;
        $date_ = new \Joomla\CMS\Date\Date($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $submitted = $date_->format('Y-m-d H:i:s', true);
        $date_stamp = $date_->format('YmdHis', true);

        $lines[$lineNum]['ZZZ_B_SUBMITTED'][] = $submitted;
        $lines[$lineNum]['ZZZ_C_IP'][] = $this->ip;
        $lines[$lineNum]['ZZZ_D_BROWSER'][] = $this->browser;
        $lines[$lineNum]['ZZZ_E_OPSYS'][] = $this->opsys;

        $xmldata = $this->xmldata;
        if ($mailback) {
            $xmldata = $this->mb_xmldata;
        }

        $processed = array();
        if (count($xmldata)) {
            foreach ($xmldata as $data) {
                if (!in_array($data[_FF_DATA_NAME], $filter) && !in_array($data[_FF_DATA_NAME], $processed)) {
                    $fields[strip_tags($data[_FF_DATA_TITLE])] = true;
                    $lines[$lineNum][strip_tags($data[_FF_DATA_TITLE])][] = is_array($data[_FF_DATA_VALUE]) ? implode('|', $data[_FF_DATA_VALUE]) : $data[_FF_DATA_VALUE];
                    //$processed[] = $data[_FF_DATA_NAME];
                }
            } // foreach
        }

        $head = '';
        ksort($fields);
        $lineLength = count($lines);
        foreach ($fields as $fieldName => $null) {
            if ($inverted == false) {
                $head .= $csvquote . $fieldName . $csvquote . $csvdelimiter;
            }
        }

        if ($inverted == false) {
            $head = substr($head, 0, strlen($head) - 1) . nl();
        }

        $out = '';
        for ($i = 0; $i < $lineLength; $i++) {
            ksort($lines[$i]);
            foreach ($lines[$i] as $fieldName => $line) {
                if ($inverted == true) {
                    $out .= $csvquote . str_replace($csvquote, $csvquote . $csvquote, str_replace("\n", $cellnewline, str_replace("\r", "", $fieldName))) . $csvquote . $csvdelimiter;
                }
                $out .= $csvquote . str_replace($csvquote, $csvquote . $csvquote, str_replace("\n", $cellnewline, str_replace("\r", "", implode('|', $line)))) . $csvquote . $csvdelimiter;

                if ($inverted == true) {
                    $out .= nl();
                }
            }

            if ($inverted == false) {
                $out = substr($out, 0, strlen($out) - 1);
                $out .= nl();
            }
        }
        mt_srand();
        $csvname = $this->uploads . '/ffexport-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.csv';
        File::makeSafe($csvname);
        if (function_exists('mb_convert_encoding')) {
            $to_encoding = 'UTF-16LE';
            $from_encoding = 'UTF-8';
            $chrchr = chr(255) . chr(254) . mb_convert_encoding($head . $out, $to_encoding, $from_encoding);
            if (!File::write($csvname, $chrchr)) {
                $this->status = _FF_STATUS_ATTACHMENT_FAILED;
            } // if
        } else {
            $head_out = $head . $out;
            if (!File::write($csvname, $head_out)) {
                $this->status = _FF_STATUS_ATTACHMENT_FAILED;
            } // if
        }
        return $csvname;
    }

    function expxml($filter = array(), $mailback = false, $translate = false)
    {
        global $ff_compath, $ff_version, $mosConfig_fileperms;

        $tz = 'UTC';
        $tz = new DateTimeZone($this->app->get('offset'));

        $date_stamp = date('YmdHis');
        $submitted = $this->submitted;
        $date_file = date('Y-m-d H:i:s');
        $date_ = new \Joomla\CMS\Date\Date($this->submitted, $tz);
        $offset = $date_->getOffsetFromGMT();
        if ($offset > 0) {
            $date_->add(new DateInterval('PT' . $offset . 'S'));
        } else if ($offset < 0) {
            $offset = $offset * -1;
            $date_->sub(new DateInterval('PT' . $offset . 'S'));
        }
        $submitted = $date_->format('Y-m-d H:i:s', true);
        $date_stamp = $date_->format('YmdHis', true);
        $date_file = $submitted;

        if ($this->dying)
            return '';
        mt_srand();
        $xmlname = $this->uploads . '/ffexport-' . $date_stamp . '-' . mt_rand(0, mt_getrandmax()) . '.xml';

        $xml = '<?xml version="1.0" encoding="utf-8" ?>' . nl() .
            '<FacileFormsExport type="records" version="' . $ff_version . '">' . nl() .
            indent(1) . '<exportdate>' . $date_file . '</exportdate>' . nl();
        if ($this->record_id != '')
            $xml .= indent(1) . '<record id="' . $this->record_id . '">' . nl();
        else
            $xml .= indent(1) . '<record>' . nl();

        $title_translated = $this->getFormTitleTranslated();

        $xml .= indent(2) . '<submitted>' . $submitted . '</submitted>' . nl() .
            indent(2) . '<form>' . $this->form . '</form>' . nl() .
            indent(2) . '<title>' . htmlspecialchars($title_translated != '' ? $title_translated : $this->formrow->title, ENT_QUOTES, 'UTF-8') . '</title>' . nl() .
            indent(2) . '<name>' . $this->formrow->name . '</name>' . nl() .
            indent(2) . '<ip>' . $this->ip . '</ip>' . nl() .
            indent(2) . '<browser>' . htmlspecialchars($this->browser, ENT_QUOTES, 'UTF-8') . '</browser>' . nl() .
            indent(2) . '<opsys>' . htmlspecialchars($this->opsys, ENT_QUOTES, 'UTF-8') . '</opsys>' . nl() .
            indent(2) . '<provider>' . $this->provider . '</provider>' . nl() .
            indent(2) . '<viewed>0</viewed>' . nl() .
            indent(2) . '<exported>0</exported>' . nl() .
            indent(2) . '<archived>0</archived>' . nl();
        $processed = array();

        $xmldata = $this->xmldata;
        if ($mailback) {
            $xmldata = $this->mb_xmldata;
        }

        if (count($xmldata))
            foreach ($xmldata as $data) {

                if ($translate) {
                    $title_translated = '';
                    $this->getFieldTranslated('label', $data[_FF_DATA_NAME], $title_translated);
                }

                if (!in_array($data[_FF_DATA_NAME], $filter) && !in_array($data[_FF_DATA_NAME], $processed)) {
                    $xml .= indent(2) . '<subrecord>' . nl() .
                        indent(3) . '<element>' . $data[_FF_DATA_ID] . '</element>' . nl() .
                        indent(3) . '<name>' . $data[_FF_DATA_NAME] . '</name>' . nl() .
                        indent(3) . '<title>' . htmlspecialchars($title_translated != '' ? $title_translated : strip_tags($data[_FF_DATA_TITLE]), ENT_QUOTES, 'UTF-8') . '</title>' . nl() .
                        indent(3) . '<type>' . $data[_FF_DATA_TYPE] . '</type>' . nl() .
                        indent(3) . '<value>' . htmlspecialchars(is_array($data[_FF_DATA_VALUE]) ? implode('|', $data[_FF_DATA_VALUE]) : $data[_FF_DATA_VALUE], ENT_QUOTES, 'UTF-8') . '</value>' . nl() .
                        indent(2) . '</subrecord>' . nl();
                    //$processed[] = $data[_FF_DATA_NAME];
                }
            } // foreach
        $xml .= indent(1) . '</record>' . nl() .
            '</FacileFormsExport>' . nl();

        File::makeSafe($xmlname);
        if (!File::write($xmlname, $xml)) {
            $this->status = _FF_STATUS_ATTACHMENT_FAILED;
        } // if

        return $xmlname;
    }

    // expxml

}
