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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\FormDisplayContextResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\FormPathResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RequestMetadataResolver;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\SubmissionTimestampFactory;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\CodeToolsRuntime;
use Vcmb\Component\BreezingformsNG\Site\Service\Scripting\ScriptingEngine;
use Vcmb\Component\BreezingformsNG\Site\Service\Export\ExportEngine;
use Vcmb\Component\BreezingformsNG\Site\Service\Notification\NotificationEngine;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\RenderingEngine;
use Vcmb\Component\BreezingformsNG\Site\Service\Submission\SubmissionEngine;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadError;
use Vcmb\Component\BreezingformsNG\Site\Service\Upload\UploadRuntime;
use Vcmb\Component\BreezingformsNG\Site\Table\FormTable;

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
                                            $trc .= Text::_('COM_BREEZINGFORMSNG_PROCESS_UNKNOWN');
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
    $database = $ff_processor->database;

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
                    $url .= '&task=quickmode.display' .
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
                    $url .= '&task=quickmode.display' .
                        '&form=' . $ff_processor->form .
                        '&page=' . $page;
                    if ($ff_processor->formrow->package != '')
                        $url .= '&pkg=' . urlencode($ff_processor->formrow->package);
                    if ($pane > 0)
                        $url .= '&tabpane=' . $pane;
                    break;
                case 'p':
                    $package = '';
                    $piecesQuery = $database->getQuery(true)
                        ->select(['name', 'package'])
                        ->from('#__facileforms_pieces')
                        ->where($database->quoteName('id') . ' = :id')
                        ->bind(':id', $id, ParameterType::INTEGER);
                    $database->setQuery($piecesQuery);
                    $rows = $database->loadObjectList();
                    if (count($rows)) {
                        $package = $rows[0]->package;
                        $what = $rows[0]->name;
                    }
                    $what = 'piece ' . $what;
                    $url .= '&task=pieces.edit' .
                        '&ids[]=' . $id;
                    if ($package != '')
                        $url .= '&pkg=' . urlencode($package);
                    break;
                case 's':
                    $package = '';
                    $scriptsQuery = $database->getQuery(true)
                        ->select(['name', 'package'])
                        ->from('#__facileforms_scripts')
                        ->where($database->quoteName('id') . ' = :id')
                        ->bind(':id', $id, ParameterType::INTEGER);
                    $database->setQuery($scriptsQuery);
                    $rows = $database->loadObjectList();
                    if (count($rows)) {
                        $package = $rows[0]->package;
                        $what = $rows[0]->name;
                    }
                    $what = 'script ' . $what;
                    $url .= '&task=scripts.edit' .
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

class HTML_facileFormsProcessor
{
    public $okrun = null;     // running is allowed
    public $ip = null;     // visitor ip
    public $agent = null;     // visitor agent
    public $browser = null;     // visitors browser
    public $opsys = null;     // visitors operating system
    public $provider = null;     // visitors provider
    public $submitted = null;     // submit date/time
    public $formrow = null;     // form row
    public $form = null;     // form #
    public $form_id = null;     // html form id
    public $page = null;     // page id
    public $target = null;     // target form name
    public $rows = null;     // element rows
    public $rowcount = null;     // # of element rows
    public $runmode = null;     // current run mode _FF_RUNMODE_...
    public $inline = null;     // inline preview
    public $inframe = null;     // running in a frame
    public $template = null;     // 0-frontend 1-backend
    public $homepage = null;     // home page
    public $mospath = null;     // mos absolute path
    public $mossite = null;     // mos site
    public $images = null;     // ff_images path
    public $uploads = null;     // ff_uploads path
    public $border = null;     // show border
    public $align = null;     // form alignment
    public $top = null;     // top margin
    public $suffix = null;     // class name suffix
    public $status = null;     // submit return status
    public $message = null;     // submit return message
    public $record_id = null;     // id of saved record
    public $submitdata = null;     // submitted data
    public $savedata = null;     // data for db save
    public $maildata = null;     // data for mail notification
    public $sfdata = null;
    public $xmldata = null;     // data for xml attachment
    public $mb_xmldata = null;     // data for mailback attachments
    public $queryCols = null;     // query column definitions
    public $queryRows = null;     // query rows
    public $showgrid = null;     // show grid in preview
    public $findtags = null;     // tags to be replaced
    public $replacetags = null;     // tag replacements
    public $dying = null;     // form is dying
    public $errrep = null;     // remember old error reporting
    public $traceMode = null;     // trace mode
    public $traceStack = null;     // trace stack
    public $traceBuffer = null;     // trace buffer
    public $user_id = null;
    public $username = null;
    public $user_full_name = null;
    public $mailbackRecipients = array();
    public $editable = null;
    public $editable_override = null;
    public $sendNotificationAfterPayment = false;
    public $opt_token = "9562384751";
    public $draggableDivIds = array();
    public $quickmode = null;
    public $app;
    public $database;
    private ?UploadRuntime $uploadRuntimeService = null;
    private ?CodeToolsRuntime $codeToolsRuntimeService = null;
    private ?ScriptingEngine $scriptingEngineService = null;
    private ?ExportEngine $exportEngineService = null;
    private ?NotificationEngine $notificationEngineService = null;
    private ?RenderingEngine $renderingEngineService = null;
    private ?SubmissionEngine $submissionEngineService = null;
    private MailerFactoryInterface $mailerFactory;
    private CacheControllerFactoryInterface $cacheControllerFactory;

    public function header()
    {
        return $this->renderingEngine()->header();
    }

    public function cbCreatePathByTokens($path, array $rows, $fieldName)
    {
        return $this->renderingEngine()->cbCreatePathByTokens($path, $rows, $fieldName);
    }

    public function makeSafeFolder($path)
    {
        return $this->renderingEngine()->makeSafeFolder($path);
    }

    public function cbCheckPermissions()
    {
        return $this->renderingEngine()->cbCheckPermissions();
    }

    public function view(): void
    {
        $this->renderingEngine()->view();
    }

    private function renderingEngine(): RenderingEngine
    {
        return $this->renderingEngineService ??= new RenderingEngine($this);
    }

    public function collectSubmitdata($cbResult = null)
    {
        return $this->submissionEngine()->collectSubmitdata($cbResult);
    }

    public function submit()
    {
        return $this->submissionEngine()->submit();
    }

    public function removeDangerousHtml($value)
    {
        return $this->submissionEngine()->removeDangerousHtml($value);
    }

    private function submissionEngine(): SubmissionEngine
    {
        return $this->submissionEngineService ??= new SubmissionEngine($this, $this->mailerFactory);
    }

    public function sendEmailNotification()
    {
        return $this->notificationEngine()->sendEmailNotification();
    }

    public function getFormTitleTranslated()
    {
        return $this->notificationEngine()->getFormTitleTranslated();
    }

    public function getFieldTranslated($field, $name, &$res, $dataObject = null, $childrenLength = 0)
    {
        return $this->notificationEngine()->getFieldTranslated(
            $field,
            $name,
            $res,
            $dataObject,
            $childrenLength
        );
    }

    public function sendMailbackNotification()
    {
        return $this->notificationEngine()->sendMailbackNotification();
    }

    public function sendSalesforceNotification()
    {
        return $this->notificationEngine()->sendSalesforceNotification();
    }

    public function sendMailChimpNotification()
    {
        return $this->notificationEngine()->sendMailChimpNotification();
    }

    private function notificationEngine(): NotificationEngine
    {
        return $this->notificationEngineService ??= new NotificationEngine($this);
    }

    public function logToDatabase($cbResult = null)
    {
        return $this->exportEngine()->logToDatabase($cbResult);
    }

    public function random_str(
        int $length,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string
    {
        return $this->exportEngine()->random_str($length, $keyspace);
    }

    public function sendMail($from, $fromname, $recipient, $subject, $body, $attachment = null, $html = null, $cc = null, $bcc = null, $alt_sender = '')
    {
        return $this->exportEngine()->sendMail(
            $from,
            $fromname,
            $recipient,
            $subject,
            $body,
            $attachment,
            $html,
            $cc,
            $bcc,
            $alt_sender
        );
    }

    public function endsWith(string $haystack, string $needle): bool
    {
        return $this->exportEngine()->endsWith($haystack, $needle);
    }

    public function exppdf($filter = [], $mailback = false, $translate = true)
    {
        return $this->exportEngine()->exppdf($filter, $mailback, $translate);
    }

    public function expcsv($filter = [], $mailback = false)
    {
        return $this->exportEngine()->expcsv($filter, $mailback);
    }

    public function expxml($filter = [], $mailback = false, $translate = false)
    {
        return $this->exportEngine()->expxml($filter, $mailback, $translate);
    }

    private function exportEngine(): ExportEngine
    {
        return $this->exportEngineService ??= new ExportEngine(
            $this,
            $this->mailerFactory,
            $this->cacheControllerFactory
        );
    }

    public function getPieceById($id, $name = null)
    {
        return $this->scriptingEngine()->getPieceById($id, $name);
    }

    public function getPieceByName($name, $id = null)
    {
        return $this->scriptingEngine()->getPieceByName($name, $id);
    }

    public function execPiece($code, $name, $type, $id, $pane)
    {
        return $this->scriptingEngine()->execPiece($code, $name, $type, $id, $pane);
    }

    public function execPieceById($id)
    {
        return $this->scriptingEngine()->execPieceById($id);
    }

    public function execPieceByName($name)
    {
        return $this->scriptingEngine()->execPieceByName($name);
    }

    public function replaceCode($code, $name, $type, $id, $pane)
    {
        return $this->scriptingEngine()->replaceCode($code, $name, $type, $id, $pane);
    }

    public function compileQueryCol(&$elem, &$coldef)
    {
        return $this->scriptingEngine()->compileQueryCol($elem, $coldef);
    }

    public function execQueryValue($code, &$elem, &$row, &$coldef, $value)
    {
        return $this->scriptingEngine()->execQueryValue($code, $elem, $row, $coldef, $value);
    }

    public function execQuery(&$elem, &$valrows, &$coldefs)
    {
        return $this->scriptingEngine()->execQuery($elem, $valrows, $coldefs);
    }

    public function script2clause(&$row)
    {
        return $this->scriptingEngine()->script2clause($row);
    }

    public function loadBuiltins(&$library)
    {
        return $this->scriptingEngine()->loadBuiltins($library);
    }

    public function loadScripts(&$library)
    {
        return $this->scriptingEngine()->loadScripts($library);
    }

    public function compressJavascript($str)
    {
        return $this->scriptingEngine()->compressJavascript($str);
    }

    public function linkcode($func, &$library, &$linked, $code, $type = null, $id = null, $pane = null)
    {
        return $this->scriptingEngine()->linkcode($func, $library, $linked, $code, $type, $id, $pane);
    }

    public function addFunction($cond, $id, $name, $code, &$library, &$linked, $type, $rowid, $pane)
    {
        return $this->scriptingEngine()->addFunction(
            $cond,
            $id,
            $name,
            $code,
            $library,
            $linked,
            $type,
            $rowid,
            $pane
        );
    }

    private function scriptingEngine(): ScriptingEngine
    {
        return $this->scriptingEngineService ??= new ScriptingEngine($this);
    }

    public function dispTraceMode($mode)
    {
        return $this->codeToolsRuntime()->dispTraceMode($mode);
    }

    public function trim(&$code)
    {
        return $this->codeToolsRuntime()->trim($code);
    }

    public function nonblank(&$code)
    {
        return $this->codeToolsRuntime()->nonblank($code);
    }

    public function getClassName($classdef)
    {
        return $this->codeToolsRuntime()->getClassName($classdef);
    }

    public function expJsValue($mixed, $indent = '')
    {
        return $this->codeToolsRuntime()->expJsValue($mixed, $indent);
    }

    public function expJsVar($name, $mixed)
    {
        return $this->codeToolsRuntime()->expJsVar($name, $mixed);
    }

    public function dumpTrace()
    {
        $this->codeToolsRuntime()->dumpTrace();
    }

    public function traceEval($name)
    {
        $this->codeToolsRuntime()->traceEval($name);
    }

    public function suicide()
    {
        return $this->codeToolsRuntime()->suicide();
    }

    public function bury()
    {
        return $this->codeToolsRuntime()->bury();
    }

    public function findToken(&$code, &$spos, &$offs)
    {
        return $this->codeToolsRuntime()->findToken($code, $spos, $offs);
    }

    public function findRealToken(&$code, &$spos, &$offs, &$line)
    {
        return $this->codeToolsRuntime()->findRealToken($code, $spos, $offs, $line);
    }

    public function patchCode($mode, $code, $name, $type, $id, $pane)
    {
        return $this->codeToolsRuntime()->patchCode($mode, $code, $name, $type, $id, $pane);
    }

    public function prepareEvalCode(&$code, $name, $type, $id, $pane)
    {
        return $this->codeToolsRuntime()->prepareEvalCode($code, $name, $type, $id, $pane);
    }

    private function codeToolsRuntime(): CodeToolsRuntime
    {
        return $this->codeToolsRuntimeService ??= new CodeToolsRuntime($this);
    }

    /**
     * Historical upload facade retained for custom PHP stored with forms.
     */
    public function saveUpload($filename, $userfile_name, $destpath, $timestamp, $useUrl = false, $useUrlDownloadDirectory = '', $resize_target_width = 0, $resize_target_height = 0, $resize_type = '', $resize_bgcolor = '#ffffff', $field_name = '')
    {
        global $mosConfig_fileperms;

        if ($this->dying) {
            return '';
        }

        $identity = $this->app->getIdentity();
        $filemode = isset($mosConfig_fileperms)
            ? ($mosConfig_fileperms === '' ? null : octdec($mosConfig_fileperms))
            : 0644;
        $result = $this->uploadRuntime()->store(
            (string) $filename,
            (string) $userfile_name,
            (string) $destpath,
            $this->findtags,
            $this->replacetags,
            $this->rows,
            (string) $this->submitted,
            (string) $this->app->get('offset'),
            [
                'username' => $identity->get('username'),
                'id' => $identity->get('id'),
                'name' => $identity->get('name'),
            ],
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
        return $this->uploadRuntime()->imageType((string) $filename);
    }

    public function resizeFile($path, $width, $height, $bgcolor = '#ffffff', $type = '')
    {
        $this->uploadRuntime()->resizeFile(
            (string) $path,
            (int) $width,
            (int) $height,
            $bgcolor === null ? null : (string) $bgcolor,
            (string) $type
        );
    }

    public function resize_image($source_image, $destination_width, $destination_height, $type = 0, $bgcolor = [0, 0, 0])
    {
        return $this->uploadRuntime()->resizeImage(
            $source_image,
            (int) $destination_width,
            (int) $destination_height,
            (int) $type,
            (array) $bgcolor
        );
    }

    public function returnBytes($val)
    {
        return $this->uploadRuntime()->parseByteSize((string) $val);
    }

    public function findQuickModeElement(array $dataObject, $needle)
    {
        return $this->uploadRuntime()->findQuickModeElement($dataObject, (string) $needle);
    }

    public function measureTime()
    {
        $time = explode(' ', microtime());

        return ((float) $time[0] + (float) $time[1]) / 1000;
    }

    private function uploadRuntime(): UploadRuntime
    {
        return $this->uploadRuntimeService ??= new UploadRuntime($this->app->getInput());
    }

    function __construct(
        CMSApplication $application,
        DatabaseInterface $database,
        MailerFactoryInterface $mailerFactory,
        CacheControllerFactoryInterface $cacheControllerFactory,
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
        $this->database = $database;
        $this->mailerFactory = $mailerFactory;
        $this->cacheControllerFactory = $cacheControllerFactory;
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
        $this->app = $application;

        $requestMetadata = (new RequestMetadataResolver(Browser::getInstance()))->resolve(
            $this->app->getInput()->server->getString('REMOTE_ADDR', ''),
            (string) $ff_config->disable_ip === '1',
            (int) $ff_config->getprovider !== 0,
            Text::_('COM_BREEZINGFORMSNG_PROCESS_UNKNOWN')
        );
        $this->ip = $requestMetadata->ip;
        $this->agent = $requestMetadata->agent;
        $this->browser = $requestMetadata->browser;
        $this->opsys = $requestMetadata->platform;
        $this->provider = $requestMetadata->provider;


        $this->submitted = (new SubmissionTimestampFactory())->create((string) $this->app->get('offset'));

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

        $this->formrow = new FormTable($this->database);
        $this->formrow->load($form);

        if ($this->formrow->published) {
            $formId = (int) $this->form;
            $query = $this->database->getQuery(true)
                ->select('*')
                ->from($this->database->quoteName('#__facileforms_elements'))
                ->where($this->database->quoteName('form') . ' = :formId')
                ->where($this->database->quoteName('published') . ' = 1')
                ->order([
                    $this->database->quoteName('page'),
                    $this->database->quoteName('ordering'),
                ])
                ->bind(':formId', $formId, ParameterType::INTEGER);
            $this->database->setQuery($query);
            $this->rows = $this->database->loadObjectList();
            $this->rowcount = count($this->rows);
        } // if
        $displayContext = (new FormDisplayContextResolver())->resolve(
            (int) $runmode,
            (bool) $this->inframe,
            (int) $form,
            (int) $this->formrow->runmode,
            (bool) $this->formrow->published,
            (int) $this->formrow->prevmode,
            (int) $ff_config->gridshow === 1,
            (int) $ff_config->gridsize,
            (string) $ff_mossite
        );
        $this->inline = $displayContext->inline;
        $this->template = $displayContext->template;
        $this->form_id = $displayContext->formId;
        $this->homepage = $displayContext->homepage;
        $this->mospath = $ff_mospath;
        $this->mossite = $ff_mossite;
        $formPaths = (new FormPathResolver())->resolve(
            (int) $this->page,
            (int) $this->formrow->pages,
            (string) $this->formrow->name,
            (string) $this->formrow->title,
            (string) $this->homepage,
            (string) $this->mospath,
            (string) $this->mossite,
            (string) $ff_config->images,
            (string) $ff_config->uploads
        );
        $this->findtags = $formPaths->tokens;
        $this->replacetags = $formPaths->values;
        $this->images = $formPaths->images;
        $this->uploads = $formPaths->uploads;
        $this->showgrid = $displayContext->showGrid;
        $this->okrun = $displayContext->canRun;
        $this->traceMode = _FF_TRACEMODE_FIRST;
        $this->traceStack = array();
        $this->traceBuffer = null;
    }

    //  HTML_facileFormsProcessor

}
