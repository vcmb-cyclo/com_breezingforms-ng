<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * */
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

class bfMobile
{
    public $isMobile = false;
}

$ff_processor = null;

define('_FF_PACKBREAKAFTER', 250);

define('_FF_STATUS_OK', 0);
define('_FF_STATUS_UNPUBLISHED', 1);
define('_FF_STATUS_SAVERECORD_FAILED', 2);
define('_FF_STATUS_SAVESUBRECORD_FAILED', 3);
define('_FF_STATUS_UPLOAD_FAILED', 4);
define('_FF_STATUS_SENDMAIL_FAILED', 5);
define('_FF_STATUS_ATTACHMENT_FAILED', 6);
define('_FF_STATUS_CAPTCHA_FAILED', 7);
define('_FF_STATUS_FILE_EXTENSION_NOT_ALLOWED', 8);
define('_FF_STATUS_SALESFORCE_SOAP_ERROR', 9);

define('_FF_DATA_ID', 0);
define('_FF_DATA_NAME', 1);
define('_FF_DATA_TITLE', 2);
define('_FF_DATA_TYPE', 3);
define('_FF_DATA_VALUE', 4);
define('_FF_DATA_FILE_SERVERPATH', 5);

define('_FF_IGNORE_STRICT', 1);
define('_FF_TRACE_NAMELIMIT', 100);

// tracemode bits
define('_FF_TRACEMODE_EVAL', 8);
define('_FF_TRACEMODE_PIECE', 16);
define('_FF_TRACEMODE_FUNCTION', 32);
define('_FF_TRACEMODE_MESSAGE', 64);
define('_FF_TRACEMODE_LOCAL', 128);
define('_FF_TRACEMODE_DIRECT', 256);
define('_FF_TRACEMODE_APPEND', 512);
define('_FF_TRACEMODE_DISABLE', 1024);
define('_FF_TRACEMODE_FIRST', 2048);

// tracemode masks
define('_FF_TRACEMODE_PRIORITY', 7);
define('_FF_TRACEMODE_TOPIC', 120);
define('_FF_TRACEMODE_VARIABLE', 248);

// debugging flags
define('_FF_DEBUG_PATCHEDCODE', 1);
define('_FF_DEBUG_ENTER', 2);
define('_FF_DEBUG_EXIT', 4);
define('_FF_DEBUG_DIRECTIVE', 8);
define('_FF_DEBUG', 0);

$cbngBasePath = JPATH_ADMINISTRATOR . '/components/com_contentbuilderng';
if (is_file($cbngBasePath . '/com_contentbuilderng.xml')) {
    require_once $cbngBasePath . '/src/Helper/ContentbuilderngHelper.php';
    require_once $cbngBasePath . '/src/Helper/FormSourceFactory.php';
    require_once $cbngBasePath . '/src/Service/ArticleService.php';
    require_once $cbngBasePath . '/src/Service/ListSupportService.php';
    require_once $cbngBasePath . '/src/Service/PermissionService.php';
}

function ff_trace($msg = null)
{
    global $ff_processor;

    if (
        $ff_processor->dying ||
        ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE) ||
        !($ff_processor->traceMode & _FF_TRACEMODE_MESSAGE)
    )
        return;
    $level = count($ff_processor->traceStack);
    $trc = '';
    for ($l = 0; $l < $level; $l++)
        $trc .= '  ';
    $trc .= Text::_('COM_BREEZINGFORMSNG_PROCESS_MSGUNKNOWN') . ": $msg\n";
    $ff_processor->traceBuffer .= htmlspecialchars($trc, ENT_QUOTES);
    if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
        $ff_processor->dumpTrace();
}

// ff_trace

function _ff_trace($line, $msg = null)
{
    global $ff_processor;

    // version for patched code
    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $level = count($ff_processor->traceStack);
    if ($msg && ($ff_processor->traceMode & _FF_TRACEMODE_MESSAGE)) {
        $trc = '';
        for ($l = 0; $l < $level; $l++)
            $trc .= '  ';
        $trc .= Text::_('COM_BREEZINGFORMSNG_PROCESS_LINE') . " $line: $msg\n";
        $ff_processor->traceBuffer .= htmlspecialchars($trc, ENT_QUOTES);
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    if ($level)
        $ff_processor->traceStack[$level - 1][3] = $line;
}

// _ff_trace

function _ff_getMode(&$newmode, &$name)
{
    global $ff_processor;

    $oldmode = $ff_processor->traceMode;
    if (_FF_DEBUG & _FF_DEBUG_ENTER)
        $ff_processor->traceBuffer .= htmlspecialchars(
            "\n_FF_DEBUG_ENTER:" .
            "\n  Name              = $name" .
            "\n  Old mode before   = " . $ff_processor->dispTraceMode($oldmode) .
            "\n  New mode before   = " . $ff_processor->dispTraceMode($newmode),
            ENT_QUOTES
        );
    if (is_null($newmode) || ($newmode & _FF_TRACEMODE_PRIORITY) < ($oldmode & _FF_TRACEMODE_PRIORITY)) {
        $newmode = $oldmode;
        $ret = $oldmode;
    } else {
        $newmode = ($oldmode & ~_FF_TRACEMODE_VARIABLE) | ($newmode & _FF_TRACEMODE_VARIABLE);
        if ($oldmode != $newmode)
            $ff_processor->traceMode = $newmode;
        $ret = ($newmode & _FF_TRACEMODE_LOCAL) ? $oldmode : $newmode;
    } // if
    if (_FF_DEBUG & _FF_DEBUG_ENTER) {
        $ff_processor->traceBuffer .= htmlspecialchars(
            "\n  Old mode compiled = " . $ff_processor->dispTraceMode($ret) .
            "\n  New mode compiled = " . $ff_processor->dispTraceMode($newmode) .
            "\n",
            ENT_QUOTES
        );
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    return $ret;
}

// _ff_getmode

function _ff_tracePiece($newmode, $name, $line, $type, $id, $pane)
{
    global $ff_processor;

    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $oldmode = _ff_getMode($newmode, $name);
    if ($newmode & _FF_TRACEMODE_PIECE) {
        $level = count($ff_processor->traceStack);
        for ($l = 0; $l < $level; $l++)
            $ff_processor->traceBuffer .= '  ';
        $ff_processor->traceBuffer .= htmlspecialchars(
            "+" . Text::_('COM_BREEZINGFORMSNG_PROCESS_ENTER') . " $name " . Text::_('COM_BREEZINGFORMSNG_PROCESS_ATLINE') . " $line\n",
            ENT_QUOTES
        );
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    array_push($ff_processor->traceStack, array($oldmode, 'p', $name, $line, $type, $id, $pane));
}

// _ff_tracePiece

function _ff_traceFunction($newmode, $name, $line, $type, $id, $pane, &$args)
{
    global $ff_processor;

    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $oldmode = _ff_getMode($newmode, $name);
    if ($newmode & _FF_TRACEMODE_FUNCTION) {
        $level = count($ff_processor->traceStack);
        $trc = '';
        for ($l = 0; $l < $level; $l++)
            $trc .= '  ';
        $trc .= "+" . Text::_('COM_BREEZINGFORMSNG_PROCESS_ENTER') . " $name(";
        if ($args) {
            $next = false;
            foreach ($args as $arg) {
                if ($next)
                    $trc .= ', ';
                else
                    $next = true;
                if (is_null($arg))
                    $trc .= 'null';
                else
                    if (is_bool($arg)) {
                        $trc .= $arg ? 'true' : 'false';
                    } else
                        if (is_numeric($arg))
                            $trc .= $arg;
                        else
                            if (is_string($arg)) {
                                $arg = preg_replace('/([\\s]+)/si', ' ', $arg);
                                if (strlen($arg) > _FF_TRACE_NAMELIMIT)
                                    $arg = substr($arg, 0, _FF_TRACE_NAMELIMIT - 3) . '...';
                                $trc .= "'$arg'";
                            } else
                                if (is_array($arg))
                                    $trc .= Text::_('COM_BREEZINGFORMSNG_PROCESS_ARRAY');
                                else
                                    if (is_object($arg))
                                        $trc .= Text::_('COM_BREEZINGFORMSNG_PROCESS_OBJECT');
                                    else
                                        if (is_resource($arg))
                                            $trc .= Text::_('COM_BREEZINGFORMSNG_PROCESS_RESOURCE');
                                        else
                                            $trc .= _FACILEFORMS_PROCESS_UNKTYPE;
            } // foreach
        } // if
        $trc .= ") " . Text::_('COM_BREEZINGFORMSNG_PROCESS_ATLINE') . " $line\n";
        $ff_processor->traceBuffer .= htmlspecialchars($trc, ENT_QUOTES);
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
    } // if
    array_push($ff_processor->traceStack, array($oldmode, 'f', $name, $line, $type, $id, $pane));
}

// _ff_traceFunction

function _ff_traceExit($line, $retval = null)
{
    global $ff_processor;

    if ($ff_processor->dying || ($ff_processor->traceMode & _FF_TRACEMODE_DISABLE))
        return;
    $info = array_pop($ff_processor->traceStack);
    if ($info) {
        $oldmode = $ff_processor->traceMode;
        $newmode = $info[0];
        $kind = $info[1];
        $name = $info[2];
        $type = $info[4];
        $id = $info[5];
        $pane = $info[6];
        if (_FF_DEBUG & _FF_DEBUG_EXIT) {
            $ff_processor->traceBuffer .= htmlspecialchars(
                "\n_FF_DEBUG_EXIT:" .
                "\n  Info     = $kind $name at line $line" .
                "\n  Old mode = " . $ff_processor->dispTraceMode($oldmode) .
                "\n  New mode = " . $ff_processor->dispTraceMode($newmode) .
                "\n",
                ENT_QUOTES
            );
            if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
                $ff_processor->dumpTrace();
        } // if
        if ($kind == 'p')
            $visible = $oldmode & _FF_TRACEMODE_PIECE;
        else
            $visible = $oldmode & _FF_TRACEMODE_FUNCTION;
        if ($visible) {
            $level = count($ff_processor->traceStack);
            for ($l = 0; $l < $level; $l++)
                $ff_processor->traceBuffer .= '  ';
            $ff_processor->traceBuffer .= htmlspecialchars(
                "-" . Text::_('COM_BREEZINGFORMSNG_PROCESS_LEAVE') . " $name " . Text::_('COM_BREEZINGFORMSNG_PROCESS_ATLINE') . " $line\n",
                ENT_QUOTES
            );
            if ($oldmode & _FF_TRACEMODE_DIRECT)
                $ff_processor->dumpTrace();
        } // if
        if ($oldmode != $newmode)
            $ff_processor->traceMode = ($oldmode & ~_FF_TRACEMODE_VARIABLE) | ($newmode & _FF_TRACEMODE_VARIABLE);
    } else {
        $ff_processor->traceBuffer .= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_PROCESS_WARNSTK') . "\n", ENT_QUOTES);
        if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
            $ff_processor->dumpTrace();
        $type = $id = $pane = null;
        $name = Text::_('COM_BREEZINGFORMSNG_PROCESS_UNKNOWN');
    } // if
    return $retval;
}

// _ff_traceExit

function _ff_errorHandler($errno, $errstr, $errfile, $errline)
{
    global $ff_processor, $ff_mossite;
    $database = Factory::getContainer()->get(DatabaseInterface::class);

    if (isset($ff_processor->dying) && $ff_processor->dying)
        return;

    $msg = "\n<strong>*** " . htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_PROCESS_EXCAUGHT'), ENT_QUOTES) . " ***</strong>\n" .
        htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_PROCESS_PHPLEVEL') . ' ', ENT_QUOTES);
    $fail = false;
    if (!defined('E_DEPRECATED')) {
        define('E_DEPRECATED', 8192);
    }
    switch ($errno) {
        case E_WARNING:
            $msg .= "E_WARNING";
            break;
        case E_NOTICE:
            $msg .= "E_NOTICE";
            break;
        case E_USER_ERROR:
            $msg .= "E_USER_ERROR";
            $fail = true;
            break;
        case E_USER_WARNING:
            $msg .= "E_USER_WARNING";
            break;
        case E_USER_NOTICE:
            $msg .= "E_USER_NOTICE";
            break;
        case E_DEPRECATED:
            $msg .= "E_DEPRECATED";
            break;
        case 2048:
            if (_FF_IGNORE_STRICT)
                return;
            $msg .= "E_STRICT";
            break;
        case 16384: // JLanguage deprecation error
            return;
            break;
        default:
            $msg .= $errno;
            $fail = true;
    } // switch
    $msg .= htmlspecialchars(
        "\n" . Text::_('COM_BREEZINGFORMSNG_PROCESS_PHPFILE') . " $errfile\n" .
        Text::_('COM_BREEZINGFORMSNG_PROCESS_PHPLINE') . " $errline\n",
        ENT_QUOTES
    );

    $n = 0;
    if (isset($ff_processor)) {
        $n = (is_countable($ff_processor->traceStack)) ? count($ff_processor->traceStack) : 1;
    }

    if ($n) {
        $info = $ff_processor->traceStack[$n - 1];
        $name = htmlspecialchars($info[2] . ' ' . Text::_('COM_BREEZINGFORMSNG_PROCESS_ATLINE') . ' ' . $info[3], ENT_QUOTES);
        $type = $info[4];
        $id = $info[5];
        $pane = $info[6];
        if ($type && $id && $ff_processor->runmode != _FF_RUNMODE_FRONTEND) {
            $url = $ff_mossite . '/administrator/index.php?option=com_breezingformsng&format=html&tmpl=component';
            $what = $id;
            switch ($type) {
                case 'f':
                    $url .= '&act=manageforms' .
                        '&task=quickmode' .
                        '&form=' . $ff_processor->form;
                    if ($ff_processor->formrow->package != '')
                        $url .= '&pkg=' . urlencode($ff_processor->formrow->package);
                    if ($pane > 0)
                        $url .= '&tabpane=' . $pane;
                    $what = 'form ' . $ff_processor->formrow->name;
                    break;
                case 'e':
                    $page = 1;
                    foreach ($ff_processor->rows as $row)
                        if ($row->id == $id) {
                            $page = $row->page;
                            $what = $row->name;
                            break;
                        } // if
                    $what = 'element ' . $what;
                    $url .= '&act=manageforms' .
                        '&task=quickmode' .
                        '&form=' . $ff_processor->form .
                        '&page=' . $page;
                    if ($ff_processor->formrow->package != '')
                        $url .= '&pkg=' . urlencode($ff_processor->formrow->package);
                    if ($pane > 0)
                        $url .= '&tabpane=' . $pane;
                    break;
                case 'p':
                    $package = '';
                    $database->setQuery("select name, package from #__facileforms_pieces where id=$id");
                    $rows = $database->loadObjectList();
                    if (count($rows)) {
                        $package = $rows[0]->package;
                        $what = $rows[0]->name;
                    }
                    $what = 'piece ' . $what;
                    $url .= '&act=managepieces' .
                        '&task=edit' .
                        '&ids[]=' . $id;
                    if ($package != '')
                        $url .= '&pkg=' . urlencode($package);
                    break;
                case 's':
                    $package = '';
                    $database->setQuery("select name, package from #__facileforms_scripts where id=$id");
                    $rows = $database->loadObjectList();
                    if (count($rows)) {
                        $package = $rows[0]->package;
                        $what = $rows[0]->name;
                    }
                    $what = 'script ' . $what;
                    $url .= '&act=managescripts' .
                        '&task=edit' .
                        '&ids[]=' . $id;
                    if ($package != '')
                        $url .= '&pkg=' . urlencode($package);
                    break;
                default:
                    $url = null;
            } // switch
            if ($url)
                $name = '<a href="#" ' .
                    'onMouseOver="window.status=\'Open ' . $what . '\';return true;" ' .
                    'onMouseOut="window.status=\'\';return true;" ' .
                    'onClick="ff_redirectParent(\'' . htmlspecialchars($url, ENT_QUOTES) . '\');return true;"' .
                    '>' . $name . '</a>';
        } // if
        $msg .= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_PROCESS_LASTPOS'), ENT_QUOTES) . ' ' . $name . "\n";
    } // if
    $msg .= htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_PROCESS_ERRMSG') . " $errstr\n\n", ENT_QUOTES);
    if ($fail) {
        if (isset($ff_processor)) {
            $ff_processor->traceBuffer .= $msg;
            $ff_processor->suicide();
        }
    } else
        if (isset($ff_processor)) {
            if (($ff_processor->traceMode & _FF_TRACEMODE_DISABLE) == 0) {
                $ff_processor->traceBuffer .= $msg;
                if ($ff_processor->traceMode & _FF_TRACEMODE_DIRECT)
                    $ff_processor->dumpTrace();
            }
        } // if
}

// _ff_errorHandler

require_once __DIR__ . '/legacy/processor/bfProcessorCodeTools.php';
require_once __DIR__ . '/legacy/processor/bfProcessorScripting.php';
require_once __DIR__ . '/legacy/processor/bfProcessorRendering.php';
require_once __DIR__ . '/legacy/processor/bfProcessorExports.php';
require_once __DIR__ . '/legacy/processor/bfProcessorNotifications.php';
require_once __DIR__ . '/legacy/processor/bfProcessorUploads.php';
require_once __DIR__ . '/legacy/processor/bfProcessorSubmission.php';

class HTML_facileFormsProcessor
{
    use bfProcessorCodeTools;
    use bfProcessorScripting;
    use bfProcessorRendering;
    use bfProcessorExports;
    use bfProcessorNotifications;
    use bfProcessorUploads;
    use bfProcessorSubmission;


    var $okrun = null;     // running is allowed
    var $ip = null;     // visitor ip
    var $agent = null;     // visitor agent
    var $browser = null;     // visitors browser
    var $opsys = null;     // visitors operating system
    var $provider = null;     // visitors provider
    var $submitted = null;     // submit date/time
    var $formrow = null;     // form row
    var $form = null;     // form #
    var $form_id = null;     // html form id
    var $page = null;     // page id
    var $target = null;     // target form name
    var $rows = null;     // element rows
    var $rowcount = null;     // # of element rows
    var $runmode = null;     // current run mode _FF_RUNMODE_...
    var $inline = null;     // inline preview
    var $inframe = null;     // running in a frame
    var $template = null;     // 0-frontend 1-backend
    var $homepage = null;     // home page
    var $mospath = null;     // mos absolute path
    var $mossite = null;     // mos site
    var $images = null;     // ff_images path
    var $uploads = null;     // ff_uploads path
    var $border = null;     // show border
    var $align = null;     // form alignment
    var $top = null;     // top margin
    var $suffix = null;     // class name suffix
    var $status = null;     // submit return status
    var $message = null;     // submit return message
    var $record_id = null;     // id of saved record
    var $submitdata = null;     // submitted data
    var $savedata = null;     // data for db save
    var $maildata = null;     // data for mail notification
    var $sfdata = null;
    var $xmldata = null;     // data for xml attachment
    var $mb_xmldata = null;     // data for mailback attachments
    var $queryCols = null;     // query column definitions
    var $queryRows = null;     // query rows
    var $showgrid = null;     // show grid in preview
    var $findtags = null;     // tags to be replaced
    var $replacetags = null;     // tag replacements
    var $dying = null;     // form is dying
    var $errrep = null;     // remember old error reporting
    var $traceMode = null;     // trace mode
    var $traceStack = null;     // trace stack
    var $traceBuffer = null;     // trace buffer
    var $user_id = null;
    var $username = null;
    var $user_full_name = null;
    var $mailbackRecipients = array();
    var $editable = null;
    var $editable_override = null;
    var $sendNotificationAfterPayment = false;
    var $opt_token = "9562384751";
    public $draggableDivIds = array();
    public $isMobile = false;
    public $quickmode = null;
    public $legacy_wrap = true;
    var $app;
    var $database;

    function __construct(
        $runmode, // _FF_RUNMODE_FRONTEND, ..._BACKEND, ..._PREVIEW
        $inframe, // run in iframe
        $form, // form id
        $page = 1, // page #
        $border = 0, // show border
        $align = 1, // align code
        $top = 0, // top margin
        $target = '', // target form name
        $suffix = '', // class name suffix
        $editable = 0,
        $editable_override = 0
    ) {
        global $ff_config, $ff_mossite, $ff_mospath, $ff_processor;
        $ff_processor = $this;
        $this->database = Factory::getContainer()->get(DatabaseInterface::class);
        $this->dying = false;
        $this->runmode = $runmode;
        $this->inframe = $inframe;
        $this->form = $form;
        $this->page = $page;
        $this->border = $border;
        $this->align = $align;
        $this->top = $top;
        $this->target = $target;
        $this->suffix = trim($suffix);
        $this->editable = $editable;
        $this->editable_override = $editable_override;
        $this->app = Factory::getApplication();

        if (!class_exists('Joomla\CMS\Environment\Browser')) {
            require_once(JPATH_SITE . '/libraries/joomla/environment/browser.php');
        }
        $this->ip = $_SERVER['REMOTE_ADDR'];
        if ($ff_config->disable_ip == "1") {
            $this->ip = 0;
        }
        $this->agent = Browser::getInstance()->getAgentString();

        $this->browser = Browser::getInstance()->getAgentString();

        $jbrowserInstance = Browser::getInstance();
        $this->opsys = $jbrowserInstance->getPlatform();

        if ($ff_config->getprovider == 0)
            $this->provider = Text::_('COM_BREEZINGFORMSNG_PROCESS_UNKNOWN');
        else {
            $host = @GetHostByAddr($this->ip);
            $this->provider = preg_replace('/^./', '', strchr($host, '.'));
        } // if


        $tz = 'UTC';
        $tz = new DateTimeZone($this->app->get('offset'));

        $submitted = new \Joomla\CMS\Date\Date();
        $submitted = new \Joomla\CMS\Date\Date('now', $tz);

        $this->submitted = $submitted->format('Y-m-d H:i:s');

        /*
          $format = Text::_('DATE_FORMAT_LC2');
          if ( !$format ) {
          $this->submitted = date('Y-m-d H:i:s');
          }else{
          $config = Factory::getApplication()->getConfig();
          $offset = $config->getValue('config.offset');
          $instance = new \Joomla\CMS\Date\Date(date('Y-m-d H:i:s'));
          $instance->setOffset($offset);
          $this->submitted = $instance->toFormat($format);
          } */

        $this->formrow = new facileFormsForms($this->database);
        $this->formrow->load($form);

        if ($this->formrow->published) {
            $this->database->setQuery(
                "select * from #__facileforms_elements " .
                "where form=" . $this->form . " and published=1 " .
                "order by page, ordering"
            );
            $this->rows = $this->database->loadObjectList();
            $this->rowcount = count($this->rows);
        } // if
        $this->inline = 0;
        $this->template = 0;
        $this->form_id = "ff_form" . $form;
        if ($runmode == _FF_RUNMODE_FRONTEND) {
            $this->homepage = $ff_mossite;
        } else {
            if ($this->inframe) {
                $this->homepage = $ff_mossite . '/administrator/index.php?tmpl=component';
                if ($this->formrow->runmode == 2)
                    $this->template++;
            } else {
                $this->template++;
                if ($runmode == _FF_RUNMODE_PREVIEW) {
                    $this->inline = 1;
                    $this->form_id = "adminForm";
                } // if
                $this->homepage = 'index.php?tmpl=component';
            } // if
        } // if
        $this->mospath = $ff_mospath;
        $this->mossite = $ff_mossite;
        $this->findtags = array(
            '{ff_currentpage}',
            '{ff_lastpage}',
            '{ff_name}',
            '{ff_title}',
            '{ff_homepage}',
            '{mospath}',
            '{mossite}'
        );
        $this->replacetags = array(
            $this->page,
            $this->formrow->pages,
            $this->formrow->name,
            $this->formrow->title,
            $this->homepage,
            $this->mospath,
            $this->mossite
        );
        $this->images = str_replace($this->findtags, $this->replacetags, $ff_config->images);
        $this->findtags[] = '{ff_images}';
        $this->replacetags[] = $this->images;
        $this->uploads = str_replace($this->findtags, $this->replacetags, $ff_config->uploads);
        $this->findtags[] = '{ff_uploads}';
        $this->replacetags[] = $this->uploads;
        // CONTENTBUILDER
        $this->findtags[] = '{CBSite}';
        $this->replacetags[] = JPATH_SITE;
        $this->findtags[] = '{cbsite}';
        $this->replacetags[] = JPATH_SITE;
        $this->showgrid = $runmode == _FF_RUNMODE_PREVIEW && $this->formrow->prevmode > 0 && $ff_config->gridshow == 1 && $ff_config->gridsize > 1;
        $this->okrun = $this->formrow->published;

        if ($this->okrun)
            switch ($this->runmode) {
                case _FF_RUNMODE_FRONTEND:
                    $this->okrun = ($this->formrow->runmode == 0 || $this->formrow->runmode == 1);
                    break;
                case _FF_RUNMODE_BACKEND:
                    $this->okrun = ($this->formrow->runmode == 0 || $this->formrow->runmode == 2);
                    break;
                default:
                    ;
            } // switch
        $this->traceMode = _FF_TRACEMODE_FIRST;
        $this->traceStack = array();
        $this->traceBuffer = null;
    }

    //  HTML_facileFormsProcessor

}
