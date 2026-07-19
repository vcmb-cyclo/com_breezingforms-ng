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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\CMS\Mail\MailerFactoryInterface;

/**
 * Sofortueberweisung payment callbacks: success page, server-side
 * confirmation and paid-file download.
 */
final class SofortCallback
{
    public function __construct(
        private readonly CMSApplication $application,
        private readonly DatabaseInterface $database,
        private readonly RedirectHelper $redirectHelper,
        private readonly MailerFactoryInterface $mailerFactory,
    ) {
    }

    public function success(): void
    {
        global $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $database = $this->database;

        $mainframe = $this->application;
        $db = $database;



    $input = $this->application->getInput();
    $tx_token = $input->getString('tx', '');
    if ($tx_token == '') {
        $msg = Text::_("This transaction id is empty!");
        require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
    } else {

        $formId = $input->getInt('user_variable_0', '');
        $recordId = $input->getInt('user_variable_1', '');

        if ($formId != '' && $recordId != '') {


            $formIdInt = (int) $formId;
            $formQuery = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__facileforms_forms'))
                ->where($db->quoteName('id') . ' = :formIdInt')
                ->bind(':formIdInt', $formIdInt, ParameterType::INTEGER);
            $db->setQuery($formQuery);
            $list = $db->loadObjectList();
            if (count($list) == 0) {
                $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
                exit;
            }

            $form = $list[0];

            $areas = json_decode($form->template_areas, true);
            if (!is_array($areas)) {
                $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_SU_DATA'));
            }

            foreach ($areas as $area) {
                foreach ($area['elements'] as $element) {
                    if ($element['internalType'] == 'bfSofortueberweisung') {
                        $options = $element['options'];
                        if ($options['downloadableFile']) {
                            $tx_token = $input->getString('tx', '');
                            $tries = $options['downloadTries'];

                            $recordIdInt = (int) $recordId;
                            $sofortTx = 'Sofortüberweisung: ' . $input->getString('tx', '');
                            $downloadsQuery = $db->getQuery(true)
                                ->select($db->quoteName('paypal_download_tries'))
                                ->from($db->quoteName('#__facileforms_records'))
                                ->where($db->quoteName('id') . ' = :recordIdInt')
                                ->where($db->quoteName('paypal_tx_id') . ' = :sofortTx')
                                ->bind(':recordIdInt', $recordIdInt, ParameterType::INTEGER)
                                ->bind(':sofortTx', $sofortTx, ParameterType::STRING);
                            $db->setQuery($downloadsQuery);

                            $downloads = $db->loadObjectList();

                            $confirmed = false;
                            if (count($downloads) == 1) {
                                $confirmed = true;
                            }

                            require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/sofort_download.php');
                        } else {
                            if ($options['thankYouPage'] != '') {
                                $this->redirectHelper->to($options['thankYouPage']);
                            } else {
                                $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_PAYING_WITH_SU'));
                            }
                        }

                        break;
                    }
                }
            }
        } else {
            $msg = Text::_("COM_BREEZINGFORMSNG_MISSING_PAYMENT_INFORMATION");
            $tx_token = Text::_("COM_BREEZINGFORMSNG_NOT_AVAILABLE");
            if ($input->getString('tx', '') != '') {
                $tx_token = $input->getString('tx', '');
            }
            require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
        }
    }
    }

    public function confirm(): void
    {
        global $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $database = $this->database;

        $mainframe = $this->application;
        $db = $database;



    $input = $this->application->getInput();
    $formId = $input->getInt('user_variable_0', -1);
    $recordId = $input->getInt('user_variable_1', -1);


    $formIdInt = (int) $formId;
    $formQuery = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__facileforms_forms'))
        ->where($db->quoteName('id') . ' = :formIdInt')
        ->bind(':formIdInt', $formIdInt, ParameterType::INTEGER);
    $db->setQuery($formQuery);
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        exit;
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfSofortueberweisung') {

                $options = $element['options'];

                $data = array(
                    'transaction' => $input->getString('transaction', ''),
                    'user_id' => $input->getString('user_id', ''),
                    'project_id' => $input->getString('project_id', ''),
                    'sender_holder' => $input->getString('sender_holder', ''),
                    'sender_account_number' => $input->getString('sender_account_number', ''),
                    'sender_bank_code' => $input->getString('sender_bank_code', ''),
                    'sender_bank_name' => $input->getString('sender_bank_name', ''),
                    'sender_bank_bic' => $input->getString('sender_bank_bic', ''),
                    'sender_iban' => $input->getString('sender_iban', ''),
                    'sender_country_id' => $input->getString('sender_country_id', ''),
                    'recipient_holder' => $input->getString('recipient_holder', ''),
                    'recipient_account_number' => $input->getString('recipient_account_number', ''),
                    'recipient_bank_code' => $input->getString('recipient_bank_code', ''),
                    'recipient_bank_name' => $input->getString('recipient_bank_name', ''),
                    'recipient_bank_bic' => $input->getString('recipient_bank_bic', ''),
                    'recipient_iban' => $input->getString('recipient_iban', ''),
                    'recipient_country_id' => $input->getString('recipient_country_id', ''),
                    'international_transaction' => $input->getString('international_transaction', ''),
                    'amount' => $input->getString('amount', ''),
                    'currency_id' => $input->getString('currency_id', ''),
                    'reason_1' => $input->getString('reason_1', ''),
                    'reason_2' => $input->getString('reason_2', ''),
                    'security_criteria' => $input->getString('security_criteria', ''),
                    'user_variable_0' => $input->getString('user_variable_0', ''),
                    'user_variable_1' => $input->getString('user_variable_1', ''),
                    'user_variable_2' => $input->getString('user_variable_2', ''),
                    'user_variable_3' => $input->getString('user_variable_3', ''),
                    'user_variable_4' => $input->getString('user_variable_4', ''),
                    'user_variable_5' => $input->getString('user_variable_5', ''),
                    'created' => $input->getString('created', ''),
                    'project_password' => $options['project_password']
                );

                $data_implode = implode('|', $data);
                $hash = sha1($data_implode);

                $recordIdInt = (int) $recordId;
                $txidQuery = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__facileforms_records'))
                    ->where($db->quoteName('id') . ' = :recordIdInt')
                    ->where($db->quoteName('paypal_tx_id') . " = ''")
                    ->setLimit(1)
                    ->bind(':recordIdInt', $recordIdInt, ParameterType::INTEGER);
                $db->setQuery($txidQuery);
                $txid = $db->loadObjectList();

                if ($hash == $input->getString('hash', '')) {

                    if (count($txid) != 0) {

                        if ($txid[0]->paypal_tx_id == '') {

                            $sofortRecordId = (int) $recordId;
                            $sofortTxId = 'Sofortüberweisung: ' . $input->getString('transaction', '');
                            $sofortPaymentDate = date('Y-m-d H:i:s', strtotime($input->getString('created', '')));
                            $updateQuery = $db->getQuery(true)
                                ->update($db->quoteName('#__facileforms_records'))
                                ->set($db->quoteName('paypal_tx_id') . ' = :sofortTxId')
                                ->set($db->quoteName('paypal_payment_date') . ' = :sofortPaymentDate')
                                ->set($db->quoteName('paypal_testaccount') . ' = 0')
                                ->set($db->quoteName('paypal_download_tries') . ' = 0')
                                ->where($db->quoteName('id') . ' = :sofortRecordId')
                                ->bind(':sofortTxId', $sofortTxId, ParameterType::STRING)
                                ->bind(':sofortPaymentDate', $sofortPaymentDate, ParameterType::STRING)
                                ->bind(':sofortRecordId', $sofortRecordId, ParameterType::INTEGER);
                            $db->setQuery($updateQuery);

                            $db->execute();

                            $recipients = explode('###', $input->getString('user_variable_2', ''));
                            $recipientsSize = count($recipients);
                            $mailer = $this->mailerFactory->createMailer();
                            $mailer->Subject = Text::_('COM_BREEZINGFORMSNG_YOUR_PAYMENT_AT_SU');
                            $mailer->Body = Text::_('COM_BREEZINGFORMSNG_HALLO') . "\n\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_YOUR_PAYMENT_SUCCEEDED') . "\n\n";
                            $mailer->Body .= '--------------------------------------' . "\n\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_REASON1') . ': ' . $input->getString('reason_1', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_REASON2') . ': ' . $input->getString('reason_2', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_AMOUNT') . ': ' . str_replace('.', ',', $input->getString('amount', '')) . ' ' . $input->getString('currency_id', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_TRANSACTION') . ': ' . $input->getString('transaction', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_ACCOUNT_HOLDER') . ': ' . $input->getString('sender_holder', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_ACCOUNT_NUMBER') . ': ' . $input->getString('sender_account_number', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_BANK_CODE') . ': ' . $input->getString('recipient_bank_code', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_BANK_NAME') . ': ' . $input->getString('sender_bank_name', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_BIC') . ': ' . $input->getString('sender_bank_bic', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_IBAN') . ': ' . $input->getString('sender_iban', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_PAYMENT_DATE') . ': ' . $input->getString('created', '') . "\n\n";

                            $mailer->Body .= '--------------------------------------' . "\n\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_RECEIPT_FOR_YOUR_PAYMENT') . "\n\n";
                            $mailer->Body .= '--------------------------------------' . "\n\n";

                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_ACCOUNT_HOLDER') . ': ' . $input->getString('recipient_holder', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_ACCOUNT_NUMBER') . ': ' . $input->getString('recipient_account_number', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_BANK_CODE') . ': ' . $input->getString('recipient_bank_code', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_BANK_NAME') . ': ' . $input->getString('recipient_bank_name', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_BIC') . ': ' . $input->getString('recipient_bank_bic', '') . "\n";
                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_IBAN') . ': ' . $input->getString('recipient_iban', '') . "\n\n";

                            $mailer->Body .= '--------------------------------------' . "\n\n";

                            $mailer->Body .= Text::_('COM_BREEZINGFORMSNG_PAYMENT_GATEWAY_SU');

                            for ($i = 0; $i < $recipientsSize; $i++) {
                                if (bf_is_email($recipients[$i])) {
                                    $mailer->AddAddress($recipients[$i]);
                                    $mailer->Send();
                                }
                            }

                            // trigger a script after succeeded payment?
                            if (file_exists(JPATH_SITE . '/bf_sofortueberweisung_success.php')) {
                                require_once (JPATH_SITE . '/bf_sofortueberweisung_success.php');
                            }

                            // send mail after succeeded payment?
                            if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                                bf_sendNotificationByPaymentCache($formId, $recordId, 'admin');
                                bf_sendNotificationByPaymentCache($formId, $recordId, 'mailback');
                            }
                        }
                    }
                }

                break;
            }
        }
    }
    }

    public function download(): void
    {
        global $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $database = $this->database;

        $mainframe = $this->application;
        $db = $database;



    $input = $this->application->getInput();
    $formIdForDownload = $input->getInt('form', -1);
    $formQueryForDownload = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__facileforms_forms'))
        ->where($db->quoteName('id') . ' = :formIdForDownload')
        ->bind(':formIdForDownload', $formIdForDownload, ParameterType::INTEGER);
    $db->setQuery($formQueryForDownload);
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_PAYMENT_DATA'));
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfSofortueberweisung') {

                $options = $element['options'];

                if ($options['downloadableFile']) {

                    $file = $options['filepath'];

                    $downloadRecordId = $input->getInt('record_id', -1);
                    $sofortDownloadTx = 'Sofortüberweisung: ' . $input->getString('tx', '');
                    $selectQuery = $db->getQuery(true)
                        ->select($db->quoteName('paypal_download_tries'))
                        ->from($db->quoteName('#__facileforms_records'))
                        ->where($db->quoteName('id') . ' = :downloadRecordId')
                        ->where($db->quoteName('paypal_tx_id') . ' = :sofortDownloadTx')
                        ->bind(':downloadRecordId', $downloadRecordId, ParameterType::INTEGER)
                        ->bind(':sofortDownloadTx', $sofortDownloadTx, ParameterType::STRING);
                    $db->setQuery($selectQuery);

                    $downloads = $db->loadObjectList();

                    if (count($downloads) == 1) {

                        if ($downloads[0]->paypal_download_tries < $options['downloadTries']) {

                            $updateQuery = $db->getQuery(true)
                                ->update($db->quoteName('#__facileforms_records'))
                                ->set($db->quoteName('paypal_download_tries') . ' = ' . $db->quoteName('paypal_download_tries') . ' + 1')
                                ->where($db->quoteName('id') . ' = :downloadRecordId')
                                ->where($db->quoteName('paypal_tx_id') . ' = :sofortDownloadTx')
                                ->bind(':downloadRecordId', $downloadRecordId, ParameterType::INTEGER)
                                ->bind(':sofortDownloadTx', $sofortDownloadTx, ParameterType::STRING);
                            $db->setQuery($updateQuery);

                            $db->execute();

                            if (!file_exists($file)) {
                                $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_DOWNLOAD_FILE'));
                            }

                            \Vcmb\Component\BreezingformsNG\Site\Service\Support\DownloadHelper::stream($file);
                        } else {

                            $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_MAX_DOWNLOAD_TRIES_REACHED'));
                        }
                    } else {

                        $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_DOWNLOAD_NOT_POSSIBLE'));
                    }
                } else {

                    $this->redirectHelper->to(Uri::root(), Text::_('COM_BREEZINGFORMSNG_NO_DOWNLOADABLE_PRODUCT'));
                }

                break;
            }
        }
    }
    }
}
