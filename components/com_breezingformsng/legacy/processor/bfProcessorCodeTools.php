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
 * Tracing display and legacy code patching/eval preparation.
 */
trait bfProcessorCodeTools
{
    function dispTraceMode($mode)
    {
        if (!is_int($mode))
            return $mode;
        $m = '(';
        if ($mode & _FF_TRACEMODE_FIRST)
            $m .= 'first ';
        $m .= ($mode & _FF_TRACEMODE_DIRECT ? 'direct' : ($mode & _FF_TRACEMODE_APPEND ? 'append' : 'popup'));
        if ($mode & _FF_TRACEMODE_DISABLE)
            $m .= ' disable';
        else {
            switch ($mode & _FF_TRACEMODE_PRIORITY) {
                case 0:
                    $m .= ' minimum';
                    break;
                case 1:
                    $m .= ' low';
                    break;
                case 2:
                    $m .= ' normal';
                    break;
                case 3:
                    $m .= ' high';
                    break;
                default:
                    $m .= ' maximum';
                    break;
            } // switch
            $m .= $mode & _FF_TRACEMODE_LOCAL ? ' local' : ' global';
            switch ($mode & _FF_TRACEMODE_TOPIC) {
                case 0:
                    $m .= ' none';
                    break;
                case _FF_TRACEMODE_TOPIC:
                    $m .= ' all';
                    break;
                default:
                    if ($mode & _FF_TRACEMODE_EVAL)
                        $m .= ' eval';
                    if ($mode & _FF_TRACEMODE_PIECE)
                        $m .= ' piece';
                    if ($mode & _FF_TRACEMODE_FUNCTION)
                        $m .= ' function';
                    if ($mode & _FF_TRACEMODE_MESSAGE)
                        $m .= ' message';
            } // switch
        } // if
        return $m . ')';
    }

    // dispTraceMode

    function trim(&$code)
    {
        $len = strlen($code);
        if (!$len)
            return false;
        if (strpos(" \t\r\n", $code[0]) === false && strpos(" \t\r\n", $code[$len - 1]) === false)
            return true;
        $code = trim($code);
        return $code != '';
    }

    // trim

    function nonblank(&$code)
    {
        return preg_match("/[^\\s]+/si", $code);
    }

    // nonblank

    function getClassName($classdef)
    {
        $name = '';
        if (strpos($classdef, ';') === false)
            $name = $classdef;
        else {
            $defs = explode(';', $classdef);
            $name = $defs[$this->template];
        } // if
        if ($this->trim($name))
            $name .= $this->suffix;
        return $name;
    }

    // getClassName

    function expJsValue($mixed, $indent = '')
    {
        if (is_null($mixed))
            return $indent . 'null';

        if (is_bool($mixed))
            return $mixed ? $indent . 'true' : $indent . 'false';

        if (is_numeric($mixed))
            return $indent . $mixed;

        if (is_string($mixed))
            return
                $indent . "'" .
                str_replace(
                    array("\\", "'", "\r", "<", "\n"),
                    array("\\\\", "\\'", "\\r", "\\074", "\\n'+" . nl() . $indent . "'"),
                    $mixed
                ) .
                "'";

        if (is_array($mixed)) {
            $dst = $indent . '[' . nl();
            $next = false;
            foreach ($mixed as $value) {
                if ($next)
                    $dst .= "," . nl();
                else
                    $next = true;
                $dst .= $this->expJsValue($value, $indent . "\t");
            } // foreach
            return $dst . nl() . $indent . ']';
        } // if

        if (is_object($mixed)) {
            $dst = $indent . '{' . nl();
            $arr = get_object_vars($mixed);
            $next = false;
            foreach ($arr as $key => $value) {
                if ($next)
                    $dst .= "," . nl();
                else
                    $next = true;
                $dst .= $indent . $key . ":" . nl() . $this->expJsValue($value, $indent . "\t");
            } // foreach
            return $dst . nl() . $indent . '}';
        } // if
        // not supported types
        if (is_resource($mixed))
            return $indent . "'" . BFText::_('COM_BREEZINGFORMSNG_PROCESS_RESOURCE') . "'";

        return $indent . "'" . BFText::_('COM_BREEZINGFORMSNG_PROCESS_UNKNOWN') . "'";
    }

    // expJsValue

    function expJsVar($name, $mixed)
    {
        return $name . ' = ' . $this->expJsValue($mixed) . ';' . nl();
    }

    // expJsVar

    function dumpTrace()
    {
        if ($this->traceMode & _FF_TRACEMODE_DIRECT) {
            $html = ob_get_contents();
            ob_end_clean();
            echo htmlspecialchars($html, ENT_QUOTES) . $this->traceBuffer;
            ob_start();
            $this->traceBuffer = null;
            return;
        } // if
        if (!$this->traceBuffer)
            return;
        if ($this->traceMode & _FF_TRACEMODE_APPEND) {
            echo '<pre>' . $this->traceBuffer . '</pre>';
            $this->traceBuffer = null;
            return;
        } // if
        echo
            '<script type="text/javascript">' . nl() .
            '<!--' . nl();
        if ($this->dying)
            echo 'console.log(' . json_encode($this->traceBuffer) . ')' . nl();
        echo
            '-->' . nl() .
            '</script>' . nl();
        $this->traceBuffer = null;
    }

    // dumpTrace

    function traceEval($name)
    {
        if (
            ($this->traceMode & _FF_TRACEMODE_DISABLE) ||
            !($this->traceMode & _FF_TRACEMODE_EVAL) ||
            $this->dying
        )
            return;
        
        $level = count($this->traceStack);
        for ($l = 0; $l < $level; $l++)
            $this->traceBuffer .= '  ';
        
            $this->traceBuffer .= htmlspecialchars("eval($name)\n", ENT_QUOTES);
        if ($this->traceMode & _FF_TRACEMODE_DIRECT)
            $this->dumpTrace();
    }

    // traceEval

    function suicide()
    {
        if ($this->dying)
            return false;
        $this->dying = true;
        $rep = 0;
        $this->errrep = error_reporting($rep);
        return true;
    }

    // suicide

    function bury()
    {
        if (!$this->dying)
            return false;
        if ($this->traceMode & _FF_TRACEMODE_DIRECT)
            $this->dumpTrace();
        ob_end_clean();
        if ($this->traceMode & _FF_TRACEMODE_DIRECT)
            echo '</pre>';
        else
            $this->dumpTrace();
        error_reporting($this->errrep);
        restore_error_handler();
        return true;
    }

    // bury

    function findToken(&$code, &$spos, &$offs)
    {
        $srch = '#(\bfunction\b|return[^a-zA-Z_-]|_ff_trace|ff_trace[ \\t]*\\(|//|/\*|\*/|\\\\"|\\\\\'|{|}|\(|\)|;|"|\'|\n)#si';
        $match = array();
        if (!preg_match($srch, $code, $match, PREG_OFFSET_CAPTURE, $spos))
            return '';
        $token = strtolower($match[0][0]);
        $offs = $match[0][1];
        $spos = $offs + strlen($token);
        return $token;
    }

    // findToken

    function findRealToken(&$code, &$spos, &$offs, &$line)
    {
        $linecmt = $blockcmt = false;
        $quote = null;
        for (; ; ) {
            $token = preg_replace('/[ \\t]*/', '', $this->findToken($code, $spos, $offs));
            switch ($token) {
                case '':
                    return '';
                case 'function':
                case 'return';
                case 'ff_trace(';
                case '{':
                case '}':
                case '(':
                case ')':
                case ';':
                    if (!$linecmt && !$blockcmt && !$quote)
                        return $token;
                    break;
                case "\n":
                    $line++;
                    $linecmt = false;
                    break;
                case '//':
                    if (!$blockcmt && !$quote)
                        $linecmt = true;
                    break;
                case '/*':
                    if (!$linecmt && !$quote)
                        $longcmt = true;
                    break;
                case '"':
                case "'":
                    if ($quote == $token)
                        $quote = null;
                    else
                        if (!$linecmt && !$blockcmt && !$quote)
                            $quote = $token;
                    break;
                default:
                    break;
            } // switch
        } // for
    }

    // findRealToken

    function patchCode($mode, $code, $name, $type, $id, $pane)
    {
        $flevel = $cpos = $spos = $offs = 0;
        $bye = false;
        $fstack = array();
        $line = 1;
        if ($type && $id) {
            $type = "'$type'";
            if (!$pane)
                $pane = 'null';
        } else
            $type = $id = $pane = 'null';
        $name = str_replace("'", "\\'", $name);
        $dst = "_ff_tracePiece($mode,'$name',$line,$type,$id,$pane);";
        while (!$bye) {
            switch ($this->findRealToken($code, $spos, $offs, $line)) {
                case '':
                    $bye = true;
                    break;
                case 'function':
                    $brk = false;
                    while (!$brk) {
                        // consume tokens until finding the opening bracket
                        switch ($this->findRealToken($code, $spos, $offs, $line)) {
                            case '':
                                $bye = $brk = true;
                                break;
                            case '{':
                                $dst .= substr($code, $cpos, $spos - $cpos) .
                                    '$_ff_traceArgs = func_get_args();' .
                                    '_ff_traceFunction(' . $mode . ',__FUNCTION__,' . $line . ',' . $type . ',' . $id . ',' . $pane . ',$_ff_traceArgs);' .
                                    '$_ff_traceArgs=null;';
                                $cpos = $spos;
                                if ($flevel)
                                    array_push($fstack, $flevel);
                                $flevel = 1;
                                $brk = true;
                                break;
                            default:
                                ;
                        } // switch
                    } // while
                    break;
                case 'return':
                    $dst .= substr($code, $cpos, $spos - $cpos);
                    $cpos = $spos;
                    $brk = false;
                    while (!$brk) {
                        // consume tokens until semicolon found
                        switch ($this->findRealToken($code, $spos, $offs, $line)) {
                            case '':
                                $bye = $brk = true;
                                break;
                            case ';':
                                $arg = substr($code, $cpos, $offs - $cpos);
                                if ($this->nonblank($arg))
                                    $dst .= ' _ff_traceExit(' . $line . ',' . $arg . ');';
                                else
                                    $dst .= ' _ff_traceExit(' . $line . ');';
                                $cpos = $spos;
                                $brk = true;
                                break;
                            default:
                                ;
                        } // switch
                    } // while
                    break;
                case 'ff_trace(':
                    $dst .= substr($code, $cpos, $offs - $cpos);
                    $cpos = $spos;
                    $brk = false;
                    $lvl = 0;
                    while (!$brk) {
                        // consume tokens until finding the closing bracket
                        switch ($this->findRealToken($code, $spos, $offs, $line)) {
                            case '':
                                $bye = $brk = true;
                                break;
                            case '(':
                                $lvl++;
                                break;
                            case ')':
                                if ($lvl)
                                    $lvl--;
                                else
                                    $brk = true;
                                break;
                            default:
                                ;
                        } // switch
                    } // while
                    $par = $offs == $cpos ? '' : substr($code, $cpos, $offs - $cpos);
                    $dst .= " _ff_trace($line";
                    if ($this->nonblank($par))
                        $dst .= ',';
                    break;
                case '{':
                    if ($flevel > 0)
                        $flevel++;
                    break;
                case '}';
                    if ($flevel > 0) {
                        $flevel--;
                        if (!$flevel) {
                            $dst .= substr($code, $cpos, $offs - $cpos) . ' _ff_traceExit(' . $line . ');}';
                            $cpos = $spos;
                            if (count($fstack))
                                $flevel = array_pop($fstack);
                        } // if
                    } // if
                    break;
                default:
            } // switch
        } // while
        $spos = strlen($code);
        if ($cpos < $spos)
            $dst .= substr($code, $cpos, $spos - $cpos);
        $line--;
        $dst .= "_ff_traceExit($line);";
        if (_FF_DEBUG & _FF_DEBUG_PATCHEDCODE) {
            $this->traceBuffer .= htmlspecialchars(
                "\n_FF_DEBUG_PATCHEDCODE:" .
                "\n  Mode = " . $this->dispTraceMode($mode) .
                "\n  Name = $name" .
                "\n  Link = $type $id $pane" .
                "\n------ begin patched code ------" .
                "\n$dst" .
                "\n------- end patched code -------" .
                "\n",
                ENT_QUOTES
            );
            if ($this->traceMode & _FF_TRACEMODE_DIRECT)
                $this->dumpTrace();
        } // if
        return $dst;
    }

    // patchCode

    function prepareEvalCode(&$code, $name, $type, $id, $pane)
    {
        if ($this->dying)
            return false;
        if (!$this->nonblank($code))
            return false;
        $code .= "\n/*'/*\"/**/;"; // closes all comments and strings that my be open
        $disable = ($this->traceMode & _FF_TRACEMODE_DISABLE) ? true : false;
        if (!$disable) {
            $mode = 'null';
            $srch = '#' .
                '^[\\s]*(//\+trace|/\*\+trace)' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*([\\w]+)?' .
                '[ \\t]*(\\*/|\\r\\n)?' .
                '#';
            $match = array();
            if (preg_match($srch, $code, $match)) {
                $mode = 2;
                $append = $direct = $xeval = $piece = $func = $msg = false;
                $local = $def = true;
                for ($m = 2; $m < count($match); $m++)
                    switch ($match[$m]) {
                        // disable
                        case 'dis':
                        case 'disable':
                            $disable = true;
                            break;
                        // mode
                        case 'pop':
                        case 'popup':
                            $direct = $append = false;
                            break;
                        case 'app':
                        case 'append':
                            $append = true;
                            $direct = false;
                            break;
                        case 'dir':
                        case 'direct':
                            $direct = true;
                            $append = false;
                            break;
                        // priority
                        case 'min':
                        case 'minimum':
                            $mode = 0;
                            break;
                        case 'low':
                            $mode = 1;
                            break;
                        case 'nor':
                        case 'normal':
                            $mode = 2;
                            break;
                        case 'hig':
                        case 'high':
                            $mode = 3;
                            break;
                        case 'max':
                        case 'maximum':
                            $mode = 4;
                            break;
                        // scope
                        case 'glo':
                        case 'global':
                            $local = false;
                            break;
                        case 'loc':
                        case 'local':
                            $local = true;
                            break;
                        // topics
                        case 'all':
                            $def = false;
                            $xeval = $piece = $func = $msg = true;
                            break;
                        case 'non':
                        case 'none':
                            $def = $xeval = $piece = $func = $msg = false;
                            break;
                        case 'eva':
                        case 'eval':
                            $def = false;
                            $xeval = true;
                            break;
                        case 'pie':
                        case 'piece':
                            $def = false;
                            $piece = true;
                            break;
                        case 'fun':
                        case 'function':
                            $def = false;
                            $func = true;
                            break;
                        case 'mes':
                        case 'message':
                            $def = false;
                            $msg = true;
                            break;
                        default:
                            break;
                    } // switch

                if ($def) {
                    $xeval = false;
                    $piece = $func = $msg = true;
                }
                if ($xeval)
                    $mode |= _FF_TRACEMODE_EVAL;
                if ($piece)
                    $mode |= _FF_TRACEMODE_PIECE;
                if ($func)
                    $mode |= _FF_TRACEMODE_FUNCTION;
                if ($msg)
                    $mode |= _FF_TRACEMODE_MESSAGE;
                if ($local)
                    $mode |= _FF_TRACEMODE_LOCAL;

                $first = ($this->traceMode & _FF_TRACEMODE_FIRST) ? true : false;
                if ($first) {
                    $oldMode = $this->traceMode;
                    $this->traceMode = 0;
                    if ($disable)
                        $this->traceMode |= _FF_TRACEMODE_DISABLE;
                    if ($append)
                        $this->traceMode |= _FF_TRACEMODE_APPEND;
                    if ($direct) {
                        $this->traceMode |= _FF_TRACEMODE_DIRECT;
                        $html = ob_get_contents();
                        ob_end_clean();
                        echo '<pre>' . htmlspecialchars($html, ENT_QUOTES);
                        ob_start();
                    } // if
                } else
                    $disable = false;
                if (_FF_DEBUG & _FF_DEBUG_DIRECTIVE) {
                    $_deb = "\n_FF_DEBUG_DIRECTIVE:";
                    if ($first)
                        $_deb .= "\n  Previous mode=" . $this->dispTraceMode($oldMode);
                    $_deb .= "\n  Trace mode   =" . $this->dispTraceMode($this->traceMode) .
                        "\n  New mode     =" . $this->dispTraceMode($mode) .
                        "\n";
                    $this->traceBuffer .= htmlspecialchars($_deb, ENT_QUOTES);
                    if ($this->traceMode & _FF_TRACEMODE_DIRECT)
                        $this->dumpTrace();
                } // if
            } // if trace directive
            if (!$disable) {
                if (!$name) {
                    $name = preg_replace('/([\\s]+)/si', ' ', $code);
                    if (strlen($name) > _FF_TRACE_NAMELIMIT)
                        $name = substr($code, 0, _FF_TRACE_NAMELIMIT - 3) . '...';
                } // if
                $code = $this->patchCode($mode, $code, $name, $type, $id, $pane);
            } // if
        } // if trace not disabled
        $code = str_replace($this->findtags, $this->replacetags, $code);
        return true;
    }

    // prepareEvalCode

}
