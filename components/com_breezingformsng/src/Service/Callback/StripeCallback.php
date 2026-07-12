<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Callback;

\defined('_JEXEC') or die;

use BFRequest;
use BFText;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;

/**
 * Stripe payment callbacks: checkout confirmation and paid-file download.
 */
class StripeCallback
{
    public function confirm(): void
    {
        global $database, $ff_version, $ff_config, $ff_mospath, $ff_compath, $ff_mossite, $ff_request, $ff_processor, $ff_target;

        $mainframe = Factory::getApplication();
        $db = $database;


    BFRequest::setVar('format', 'html');


    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form_id', -1)));
    $list = $db->loadObjectList();

    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);

    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_STRIPE_DATA'));
        exit;
    }

    $tx_token = BFRequest::getVar('token');
    $record_id = BFRequest::getInt('record_id');

    foreach ($areas as $area) {

        foreach ($area['elements'] as $element) {

            if ($element['internalType'] == 'bfStripe') {

                $options = $element['options'];

                \Vcmb\Component\BreezingformsNG\Administrator\Helper\VendorHelper::load();

                \Stripe\Stripe::setApiKey($options['secretKey']);
                $stripe = new \Stripe\StripeClient($options['secretKey']);

                // Create the charge on Stripe's servers - this will charge the user's card
                try {


                    $db->setQuery("
									Select paypal_tx_id From 
										#__facileforms_records 
									Where 
										id = '" . $record_id . "'
									And
										paypal_tx_id like 'Stripe:%'
									");

                    $exists = $db->loadResult();

                    if (!$exists) {

                        /* XDA if( Factory::getApplication()->getSession()->get('bf_stripe_last_payment_amount'.$record_id, null) == null ){

            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_STRIPE_AMOUNT'));
            exit;
                                } XDA */

                        $stripearray = array();
                        $stripearray = [
                            "amount" => Factory::getApplication()->getSession()->get('bf_stripe_last_payment_amount' . $record_id, null),
                            // amount in cents, again
                            "currency" => strtolower($options['currencyCode']),
                            "source" => $tx_token,
                            "description" => $options['itemname'],
                            "metadata" => array()
                            //,"metadata" => array("Order ID" => $_session_cart['order_id'])
                        ];
                        if (Factory::getApplication()->getSession()->get('emailfield', '') !== '') {
                            $stripearray += ['receipt_email' => Factory::getApplication()->getSession()->get('emailfield', '')];
                            Factory::getApplication()->getSession()->clear('emailfield');
                        }
                        //$charge = \Stripe\Charge::create( $stripearray );
                        /*
                        $charge = \Stripe\Checkout\Session::create([
                                'customer_email' => 'bff@gmail.com',
                                'billing_address_collection' => 'required',
                                'line_items' => [[
                                  'price' => 'price_1JYA3UDkYxK6vMJ2QF2S6fNh',
                                  'quantity' => 1,
                                ]],
                                'payment_method_types' => [
                                  'card',
                                ],
                                'mode' => 'payment',
                                'success_url' => 'https://firadeldibuixilapintura.cat/test/success',
                                'cancel_url' => 'https://firadeldibuixilapintura.cat/test/cancel',
                          ]);*/


                        Factory::getApplication()->getSession()->clear('bf_stripe_last_payment_amount' . $record_id);
                    } else {

                        $exploded = explode(':', $exists);
                        //$charge = \Stripe\Charge::retrieve(trim($exploded[1]));
                    }

                    //                                      $tx_token = $charge->id;
                    $session_id = $_GET['session_id'];
                    $session = $stripe->checkout->sessions->retrieve(
                        "$session_id",
                        []
                    );

                    if ($session->payment_status != 'paid') {

                        //echo $_GET['session_id'].'<br>bf dont understand new stripe and says it was diclined lol: <br>'.var_dump($session);
                        $msg = Text::_("COM_BREEZINGFORMSNG_STRIPE_DECLINED");

                        require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/error.php');
                    } else {
                        /** get payment intend id and creation time */
                        $stripe_pi_id = $session->payment_intent; //payment intent id from last session also replacing tx_token/charge->id with paymentIntents->id
                        $stripe_pi = $stripe->paymentIntents->retrieve(
                            "$stripe_pi_id",
                            []
                        );
                        $stripe_pi_create = $stripe_pi->created; // replacing charge->created with paymentIntents->created
                        // XDA BEGIN
                        // Stripe Payment Intent updates to complete its description from pi_xxx to BF item Name - pi_xxx
                        $stripe->paymentIntents->update($stripe_pi_id, ['description' => $options['itemname']]);
                        // XDA END
                        $db->setQuery("
                                                                                Update 
                                                                                        #__facileforms_records 
                                                                                Set 
                                                                                        paypal_tx_id = " . $db->Quote('Stripe: ' . strip_tags($stripe_pi_id)) . ", 
                                                                                        paypal_payment_date = " . $db->Quote(date('Y-m-d H:i:s', $stripe_pi_create)) . ",
                                                                                        paypal_testaccount = " . $db->Quote(!$stripe_pi->livemode ? 1 : 0) . ",
                                                                                        paypal_download_tries = 0
                                                                                Where 
                                                                                        id = '" . BFRequest::getInt('record_id', -1) . "'
											");

                        $db->execute();

                        // trigger a script after succeeded payment?
                        if (file_exists(JPATH_SITE . '/bf_stripe_success.php')) {
                            require_once (JPATH_SITE . '/bf_stripe_success.php');
                        }

                        // send mail after succeeded payment?
                        if (isset($options['sendNotificationAfterPayment']) && $options['sendNotificationAfterPayment']) {
                            bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'admin');
                            bf_sendNotificationByPaymentCache(BFRequest::getInt('form_id', -1), BFRequest::getInt('record_id', -1), 'mailback');
                        }

                        if ($options['downloadableFile']) {

                            $record_id = BFRequest::getInt('record_id', -1);
                            $tries = $options['downloadTries'];
                            $form_id = BFRequest::getInt('form_id', -1);
                            require_once (JPATH_SITE . '/media/breezingforms/downloadtpl/stripe_download.php');
                        } else {

                            if ($options['thankYouPage'] != '') {
                                BFRedirect($options['thankYouPage']);
                            } else {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_PAYING_WITH_STRIPE'));
                            }
                        }
                    }
                } catch (\Stripe\Exception\CardException $e) {

                    $msg = Text::_("COM_BREEZINGFORMSNG_STRIPE_DECLINED");
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


    BFRequest::setVar('format', 'raw');


    $db->setQuery("Select * From #__facileforms_forms Where id = " . $db->Quote(BFRequest::getInt('form', -1)));
    $list = $db->loadObjectList();
    if (count($list) == 0) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_FORM_DOES_NOT_EXIST'));
        exit;
    }

    $form = $list[0];

    $areas = json_decode($form->template_areas, true);
    if (!is_array($areas)) {
        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_PAYMENT_DATA'));
    }

    foreach ($areas as $area) {
        foreach ($area['elements'] as $element) {
            if ($element['internalType'] == 'bfStripe') {

                $options = $element['options'];

                if ($options['downloadableFile']) {

                    $file = $options['filepath'];

                    $db->setQuery("
									Select paypal_download_tries From 
										#__facileforms_records 
									Where 
										id = '" . BFRequest::getInt('record_id', -1) . "'
									And
										paypal_tx_id = " . $db->Quote('Stripe: ' . BFRequest::getVar('token', '')) . "
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
												id = '" . BFRequest::getInt('record_id', -1) . "'
											And
												paypal_tx_id = " . $db->Quote('Stripe: ' . BFRequest::getVar('token', '')) . "
											");

                            $db->execute();

                            if (!file_exists($file)) {
                                BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_COULD_NOT_FIND_DOWNLOAD_FILE'));
                            }

                            \Vcmb\Component\BreezingformsNG\Site\Service\Support\DownloadHelper::stream($file);
                        } else {

                            BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_MAX_DOWNLOAD_TRIES_REACHED'));
                        }
                    } else {

                        BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_DOWNLOAD_NOT_POSSIBLE'));
                    }
                } else {

                    BFRedirect(Uri::root(), BFText::_('COM_BREEZINGFORMSNG_NO_DOWNLOADABLE_PRODUCT'));
                }

                break;
            }
        }
    }
    }
}
