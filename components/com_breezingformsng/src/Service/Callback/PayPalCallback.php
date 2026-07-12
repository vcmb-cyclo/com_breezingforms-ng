<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Vcmb\Component\BreezingformsNG\Site\Service\Support\RedirectHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;

/**
 * PayPal payment callbacks: IPN notification, return-URL confirmation,
 * paid-file download and the connect interstitial message.
 */
class PayPalCallback
{
    public function confirmIpn(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;



    $input = Factory::getApplication()->getInput();
    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote($input->getInt('form_id', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        header("Status: 200 OK");
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        header("Status: 200 OK");
        exit;
    }

    foreach ($areas as $area) {

        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfPayPal') {

                $options = $element['options'];

                $auth_token = $options['token'];
                $paypal = 'https://www.paypal.com';
                if ($options['testaccount']) {
                    $paypal = 'https://www.sandbox.paypal.com';
                    $auth_token = $options['testToken'];
                }

                $req = 'cmd=_notify-validate';

                $tx_token = $input->getString('txn_id', '0');
                foreach ($_POST as $key => $value) {
                    $value = urlencode(stripslashes($value));
                    $req .= "&$key=$value";
                }

                $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

                $pointer = null;
                $res = '';

                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    $pointer = $ch;
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_URL, $paypal . '/cgi-bin/webscr');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                    curl_setopt($ch, CURLOPT_SSLVERSION, 6); //6 is for TLSV1.2

                    ob_start();
                    curl_exec($ch);
                    $res = ob_get_contents();
                } else {
                    // try fsockopen
                    $fp = fsockopen($paypal, 80, $errno, $errstr, 30);
                    $pointer = $fp;
                    fputs($fp, $header . $req);
                    $headerdone = false;
                    while (!feof($fp)) {
                        $line = fgets($fp, 1024);
                        if (strcmp($line, "\r\n") == 0) {
                            $headerdone = true;
                        } else if ($headerdone) {
                            $res .= $line;
                        }
                    }
                }

                $lines = explode("\n", $res);

                if (strcmp($lines[0], "VERIFIED") == 0) {

                    $query = "SELECT * FROM #__facileforms_records WHERE id = '" . $input->getInt('record_id', -1) . "' LIMIT 1";
                    $db->setQuery($query);
                    $txid = $db->loadObjectList();

                    if (count($txid) != 0) {

                        if ($txid[0]->paypal_tx_id == '') {

                            $db->setQuery("
										Update
											#__facileforms_records
										Set
											paypal_tx_id = " . $db->Quote('PayPal: ' . $tx_token . ' (VALID)') . ",
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s')) . ",
											paypal_testaccount = " . $db->Quote($options['testaccount'] ? 1 : 0) . ",
											paypal_download_tries = 0
										Where
											id = '" . $input->getInt('record_id', -1) . "'
											");

                            $db->execute();

                            // trigger a script after succeeded payment?
                            if (file_exists(JPATH_SITE . '/bf_paypalipn_success.php')) {
                                require_once (JPATH_SITE . '/bf_paypalipn_success.php');
                            }

                            // send mail after succeeded payment?
                            if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                bf_sendNotificationByPaymentCache($input->getInt('form_id', -1), $input->getInt('record_id', -1), 'admin');
                                bf_sendNotificationByPaymentCache($input->getInt('form_id', -1), $input->getInt('record_id', -1), 'mailback');
                            }
                        }

                        header("Status: 200 OK");
                    }

                    header("Status: 200 OK");
                } else if (strcmp($lines[0], "INVALID") == 0) {

                    $query = "SELECT * FROM #__facileforms_records WHERE id = '" . $input->getInt('record_id', -1) . "' LIMIT 1";
                    $db->setQuery($query);
                    $txid = $db->loadObjectList();

                    if (count($txid) != 0) {

                        $db->setQuery("
										Update
											#__facileforms_records
										Set
											paypal_tx_id = " . $db->Quote('PayPal: ' . $tx_token . ' (INVALID)') . ",
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s')) . ",
											paypal_testaccount = " . $db->Quote($options['testaccount'] ? 1 : 0) . ",
											paypal_download_tries = 0
										Where
											id = '" . $input->getInt('record_id', -1) . "'
											");

                        $db->execute();
                    }

                    header("Status: 200 OK");
                }

                header("Status: 200 OK");

                // should be kept open until sending the status headers
                if (function_exists('curl_init')) {
                    curl_close($pointer);
                    ob_end_clean();
                } else {
                    fclose($pointer);
                }

                break;
            }
        }
    }
    }

    public function confirm(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;



    $input = Factory::getApplication()->getInput();
    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote($input->getInt('form_id', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_PAYPAL_DATA'));
        exit;
    }

    foreach ($areas as $area) {
        $checkPP = true;
        foreach ($area['elements'] as $element) {
            if ($element['name'] == 'PayPalSelect' || $element['name'] == 'BfPaymentSelect') {
                $checkPP = false;
                break;
            }
        }
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfPayPal') {

                $options = $element['options'];

                $auth_token = $options['token'];
                $paypal = 'https://www.paypal.com';
                if ($options['testaccount']) {
                    $paypal = 'https://www.sandbox.paypal.com';
                    $auth_token = $options['testToken'];
                }

                $req = 'cmd=_notify-synch';

                $tx_token = $input->getString('tx', '0');
                $req .= "&tx=" . urlencode($tx_token) . "&at=" . urlencode($auth_token);

                $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

                if (function_exists('curl_init')) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_URL, $paypal . '/cgi-bin/webscr');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                    curl_setopt($ch, CURLOPT_SSLVERSION, 6); //6 is for TLSV1.2

                    ob_start();
                    curl_exec($ch);
                    $res = ob_get_contents();
                    curl_close($ch);
                    ob_end_clean();
                } else {
                    // try fsockopen
                    $fp = fsockopen($paypal, 80, $errno, $errstr, 30);
                    fputs($fp, $header . $req);
                    $res = '';
                    $headerdone = false;
                    while (!feof($fp)) {
                        $line = fgets($fp, 1024);
                        if (strcmp($line, "\r\n") == 0) {
                            $headerdone = true;
                        } else if ($headerdone) {
                            $res .= $line;
                        }
                    }
                    fclose($fp);
                }

                $lines = explode("\n", $res);
                $keyarray = array();

                if (strcmp($lines[0], "SUCCESS") == 0) {
                    for ($i = 1; $i < count($lines); $i++) {
                        if ($lines[$i] != "") {
                            list($key, $val) = explode("=", $lines[$i]);
                            $keyarray[urldecode($key)] = urldecode($val);
                        }
                    }

                    if ($checkPP && (($options['amount'] > 0 && $keyarray['mc_gross'] != (doubleval($options['amount']) + doubleval($options['tax']))) || $keyarray['mc_currency'] != strtoupper($options['currencyCode']))) {

                        $success = false;
                        $msg = Text::_("Payment was not correct (amount/currency)");
                        require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                    } else {

                        $query = "SELECT * FROM #__facileforms_records WHERE id = '" . $input->getInt('record_id', -1) . "' LIMIT 1";
                        $db->setQuery($query);
                        $txid = $db->loadObjectList();

                        if (count($txid) != 0) {

                            if ($txid[0]->paypal_tx_id == '') {

                                $db->setQuery("
										Update 
											#__facileforms_records 
										Set 
											paypal_tx_id = " . $db->Quote('PayPal: ' . $tx_token) . ", 
											paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s', strtotime($keyarray["payment_date"]))) . ",
											paypal_testaccount = " . $db->Quote($options['testaccount'] ? 1 : 0) . ",
											paypal_download_tries = 0
										Where 
											id = '" . $input->getInt('record_id', -1) . "'
											");

                                $db->execute();

                                // trigger a script after succeeded payment?
                                if (file_exists(JPATH_SITE . '/bf_paypal_success.php')) {
                                    require_once (JPATH_SITE . '/bf_paypal_success.php');
                                }

                                // send mail after succeeded payment?
                                if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                    bf_sendNotificationByPaymentCache($input->getInt('form_id', -1), $input->getInt('record_id', -1), 'admin');
                                    bf_sendNotificationByPaymentCache($input->getInt('form_id', -1), $input->getInt('record_id', -1), 'mailback');
                                }

                                if ($options['downloadableFile']) {

                                    $record_id = $input->getInt('record_id', -1);
                                    $tries = $options['downloadTries'];
                                    $form_id = $input->getInt('form_id', -1);
                                    require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/download.php');
                                } else {

                                    if ($options['thankYouPage'] != '') {
                                        RedirectHelper::to($options['thankYouPage']);
                                    } else {
                                        RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_PAYING_WITH_PAYPAL'));
                                    }
                                }

                                $success = true;
                            } else {
                                if ($options['downloadableFile']) {

                                    $record_id = $input->getInt('record_id', -1);
                                    $tries = $options['downloadTries'];
                                    $form_id = $input->getInt('form_id', -1);
                                    require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/download.php');
                                } else {
                                    if ($options['useIpn']) {
                                        if ($options['thankYouPage'] != '') {
                                            RedirectHelper::to($options['thankYouPage']);
                                        } else {
                                            RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_PAYING_WITH_PAYPAL'));
                                        }
                                    } else {
                                        $success = false;
                                        $msg = Text::_("This transaction was already processed");
                                        require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                                    }
                                }
                            }
                        } else {
                            $success = false;
                            $msg = Text::_("Could not find record!");
                            require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                        }
                    }
                } else if (strcmp($lines[0], "FAIL") == 0) {
                    $success = false;
                    $msg = Text::_("Verification failed");
                    require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                } else {
                    $success = false;
                    $msg = Text::_("Verification did not return any values");
                    require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                }

                break;
            }
        }
    }
    }

    public function download(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;



    $input = Factory::getApplication()->getInput();
    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote($input->getInt('form', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_PAYPAL_DATA'));
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfPayPal') {

                $options = $element['options'];

                if ($options['downloadableFile']) {

                    $file = $options['filepath'];

                    $db->setQuery("
									Select paypal_download_tries From 
										#__facileforms_records 
									Where 
										id = '" . $input->getInt('record_id', -1) . "'
									And
										( 
                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . $input->getString('tx', '')) . "
                                                                                  Or
                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . $input->getString('tx', '') . ' (VALID)') . "
                                                                                )
									");

                    $downloads = $db->loadObjectList();

                    if (count($downloads) == 1) {

                        if ($downloads[0]->paypal_download_tries < $options['downloadTries']) {

                            $db->setQuery("
											Update 
												#__facileforms_records 
											Set
												paypal_download_tries = paypal_download_tries + 1 
											Where 
												id = '" . $input->getInt('record_id', -1) . "'
											And
												(
                                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . $input->getString('tx', '')) . "
                                                                                                  Or
                                                                                                    paypal_tx_id = " . $db->Quote('PayPal: ' . $input->getString('tx', '') . ' (VALID)') . "
                                                                                                )
											");

                            $db->execute();

                            if (!file_exists($file)) {
                                RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_DOWNLOAD_FILE'));
                            }

                            \Vcmb\Component\BreezingformsNG\Site\Service\Support\DownloadHelper::stream($file);
                        } else {

                            RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_MAX_DOWNLOAD_TRIES_REACHED'));
                        }
                    } else {

                        RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_DOWNLOAD_NOT_POSSIBLE'));
                    }
                } else {

                    RedirectHelper::to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_NO_DOWNLOADABLE_PRODUCT'));
                }

                break;
            }
        }
    }
    }

    public function connectMessage(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;



    $style = '<link rel="stylesheet" href="' . Uri::root() . 'templates/' . $mainframe->getTemplate() . '/css/template.css" type="text/css" />';

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . strtolower(Factory::getApplication()->getLanguage()->getTag()) . '" lang="' . strtolower(Factory::getApplication()->getLanguage()->getTag()) . '" >
<head>' . $style . '</head>
<div class="payPalConnectMsg">
<div class="paymentConnectMsg">
' . Text::_('COM_BREEZINGFORMSNG_PLEASE_WAIT_REQUEST') . '
</div>
</div>
</body>';
    }
}
