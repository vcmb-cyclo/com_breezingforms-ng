<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Vcmb\Component\BreezingformsNG\Site\Service\Support\RedirectHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseInterface;
use Joomla\Http\Http;
use Joomla\Http\HttpFactory;

/**
 * PayPal payment callbacks: IPN notification, return-URL confirmation,
 * paid-file download and the connect interstitial message.
 */
final class PayPalCallback
{
    private readonly Http $http;
    private readonly PaymentDownloadPolicy $downloadPolicy;

    public function __construct(
        private readonly CMSApplication $application,
        private readonly DatabaseInterface $database,
        private readonly RedirectHelper $redirectHelper,
        ?Http $http = null,
        ?PaymentDownloadPolicy $downloadPolicy = null,
    ) {
        $this->http = $http ?? (new HttpFactory())->getHttp();
        $this->downloadPolicy = $downloadPolicy ?? new PaymentDownloadPolicy();
    }

    public function confirmIpn(): void
    {
        $db = $this->database;



    $input = $this->application->getInput();
    $formId = $input->getInt('form_id', -1);
    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__facileforms_forms'))
        ->where($db->quoteName('id') . ' = :formId')
        ->bind(':formId', $formId, ParameterType::INTEGER);
    $db->setQuery($query);
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        $this->application->setHeader('status', 200, true);
        $this->application->close();
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        $this->application->setHeader('status', 200, true);
        $this->application->close();
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
                foreach ($input->post->getArray() as $key => $value) {
                    if (is_array($value)) {
                        continue;
                    }

                    $value = urlencode(stripslashes($value));
                    $req .= "&$key=$value";
                }

                $verification = $this->requestVerification($paypal, $req);

                if ($verification === 'VERIFIED') {

                    $recordId = $input->getInt('record_id', -1);
                    $recordQuery = $db->getQuery(true)
                        ->select('*')
                        ->from($db->quoteName('#__facileforms_records'))
                        ->where($db->quoteName('id') . ' = :recordId')
                        ->setLimit(1)
                        ->bind(':recordId', $recordId, ParameterType::INTEGER);
                    $db->setQuery($recordQuery);
                    $txid = $db->loadObjectList();

                    if (count($txid) != 0) {

                        if ($txid[0]->paypal_tx_id == '') {

                            $paypalTxId = 'PayPal: ' . $tx_token . ' (VALID)';
                            $paymentDate = date('Y-m-d H:i:s');
                            $testaccount = $options['testaccount'] ? 1 : 0;
                            $updateQuery = $db->getQuery(true)
                                ->update($db->quoteName('#__facileforms_records'))
                                ->set($db->quoteName('paypal_tx_id') . ' = :paypalTxId')
                                ->set($db->quoteName('paypal_payment_date') . ' = :paymentDate')
                                ->set($db->quoteName('paypal_testaccount') . ' = :testaccount')
                                ->set($db->quoteName('paypal_download_tries') . ' = 0')
                                ->where($db->quoteName('id') . ' = :recordId')
                                ->bind(':paypalTxId', $paypalTxId, ParameterType::STRING)
                                ->bind(':paymentDate', $paymentDate, ParameterType::STRING)
                                ->bind(':testaccount', $testaccount, ParameterType::INTEGER)
                                ->bind(':recordId', $recordId, ParameterType::INTEGER);
                            $db->setQuery($updateQuery);

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

                        $this->application->setHeader('status', 200, true);
                    }

                    $this->application->setHeader('status', 200, true);
                } else if ($verification === 'INVALID') {

                    $recordId = $input->getInt('record_id', -1);
                    $recordQuery = $db->getQuery(true)
                        ->select('*')
                        ->from($db->quoteName('#__facileforms_records'))
                        ->where($db->quoteName('id') . ' = :recordId')
                        ->setLimit(1)
                        ->bind(':recordId', $recordId, ParameterType::INTEGER);
                    $db->setQuery($recordQuery);
                    $txid = $db->loadObjectList();

                    if (count($txid) != 0) {

                        $paypalTxId = 'PayPal: ' . $tx_token . ' (INVALID)';
                        $paymentDate = date('Y-m-d H:i:s');
                        $testaccount = $options['testaccount'] ? 1 : 0;
                        $updateQuery = $db->getQuery(true)
                            ->update($db->quoteName('#__facileforms_records'))
                            ->set($db->quoteName('paypal_tx_id') . ' = :paypalTxId')
                            ->set($db->quoteName('paypal_payment_date') . ' = :paymentDate')
                            ->set($db->quoteName('paypal_testaccount') . ' = :testaccount')
                            ->set($db->quoteName('paypal_download_tries') . ' = 0')
                            ->where($db->quoteName('id') . ' = :recordId')
                            ->bind(':paypalTxId', $paypalTxId, ParameterType::STRING)
                            ->bind(':paymentDate', $paymentDate, ParameterType::STRING)
                            ->bind(':testaccount', $testaccount, ParameterType::INTEGER)
                            ->bind(':recordId', $recordId, ParameterType::INTEGER);
                        $db->setQuery($updateQuery);

                        $db->execute();
                    }

                    $this->application->setHeader('status', 200, true);
                }

                $this->application->setHeader('status', 200, true);

                break;
            }
        }
    }
    }

    public function confirm(): void
    {
        $db = $this->database;



    $input = $this->application->getInput();
    $formId = $input->getInt('form_id', -1);
    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__facileforms_forms'))
        ->where($db->quoteName('id') . ' = :formId')
        ->bind(':formId', $formId, ParameterType::INTEGER);
    $db->setQuery($query);
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
        $this->application->close();
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_PAYPAL_DATA'));
        $this->application->close();
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

                $lines = explode("\n", $this->requestVerification($paypal, $req));
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
                        $msg = Text::_('COM_BREEZINGFORMSNG_PAYMENT_AMOUNT_CURRENCY_INVALID');
                        require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                    } else {

                        $recordId = $input->getInt('record_id', -1);
                        $recordQuery = $db->getQuery(true)
                            ->select('*')
                            ->from($db->quoteName('#__facileforms_records'))
                            ->where($db->quoteName('id') . ' = :recordId')
                            ->setLimit(1)
                            ->bind(':recordId', $recordId, ParameterType::INTEGER);
                        $db->setQuery($recordQuery);
                        $txid = $db->loadObjectList();

                        if (count($txid) != 0) {

                            if ($txid[0]->paypal_tx_id == '') {

                                $paypalTxId = 'PayPal: ' . $tx_token;
                                $paymentDate = date('Y-m-d H:i:s', strtotime($keyarray["payment_date"]));
                                $testaccount = $options['testaccount'] ? 1 : 0;
                                $updateQuery = $db->getQuery(true)
                                    ->update($db->quoteName('#__facileforms_records'))
                                    ->set($db->quoteName('paypal_tx_id') . ' = :paypalTxId')
                                    ->set($db->quoteName('paypal_payment_date') . ' = :paymentDate')
                                    ->set($db->quoteName('paypal_testaccount') . ' = :testaccount')
                                    ->set($db->quoteName('paypal_download_tries') . ' = 0')
                                    ->where($db->quoteName('id') . ' = :recordId')
                                    ->bind(':paypalTxId', $paypalTxId, ParameterType::STRING)
                                    ->bind(':paymentDate', $paymentDate, ParameterType::STRING)
                                    ->bind(':testaccount', $testaccount, ParameterType::INTEGER)
                                    ->bind(':recordId', $recordId, ParameterType::INTEGER);
                                $db->setQuery($updateQuery);

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
                                        $this->redirectHelper->to($options['thankYouPage']);
                                    } else {
                                        $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_PAYING_WITH_PAYPAL'));
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
                                            $this->redirectHelper->to($options['thankYouPage']);
                                        } else {
                                            $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_PAYING_WITH_PAYPAL'));
                                        }
                                    } else {
                                        $success = false;
                                        $msg = Text::_('COM_BREEZINGFORMSNG_PAYMENT_TRANSACTION_ALREADY_PROCESSED');
                                        require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                                    }
                                }
                            }
                        } else {
                            $success = false;
                            $msg = Text::_('COM_BREEZINGFORMSNG_PAYMENT_RECORD_NOT_FOUND');
                            require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                        }
                    }
                } else if (strcmp($lines[0], "FAIL") == 0) {
                    $success = false;
                    $msg = Text::_('COM_BREEZINGFORMSNG_PAYMENT_VERIFICATION_FAILED');
                    require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                } else {
                    $success = false;
                    $msg = Text::_('COM_BREEZINGFORMSNG_PAYMENT_VERIFICATION_EMPTY');
                    require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                }

                break;
            }
        }
    }
    }

    public function download(): void
    {
        (new PaymentDownloadService(
            $this->application,
            $this->database,
            $this->redirectHelper,
            $this->downloadPolicy
        ))->download(
            'bfPayPal',
            'COM_BREEZINGFORMSNG_COULD_NOT_FIND_PAYPAL_DATA',
            'tx',
            'PayPal',
            true
        );
    }

    public function connectMessage(): void
    {
        echo '<div class="payPalConnectMsg"><div class="paymentConnectMsg">'
            . htmlspecialchars(Text::_('COM_BREEZINGFORMSNG_PLEASE_WAIT_REQUEST'), ENT_QUOTES, 'UTF-8')
            . '</div></div>';
    }

    private function requestVerification(string $paypalUrl, string $body): string
    {
        try {
            $response = $this->http->post(
                $paypalUrl . '/cgi-bin/webscr',
                $body,
                ['Content-Type' => 'application/x-www-form-urlencoded'],
            );

            return trim((string) $response->getBody());
        } catch (\RuntimeException) {
            return '';
        }
    }
}
