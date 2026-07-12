<?php

/**
 * BreezingForms NG - A Joomla Forms Application
 *
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Front controller of the legacy form engine. Boots the legacy runtime,
 * then dispatches either to the form renderer (view/submit) or to one of
 * the callback services (payments, captcha, uploads, opt-in/out).
 * */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\CaptchaCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\FlashUploadCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\OptCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\PayPalCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\SofortCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\StripeCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\FormRenderer;

if (!function_exists('bf_b64enc')) {

    function bf_b64enc($str)
    {
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base . $sixty_four, $str);
    }

}

if (!function_exists('bf_b64dec')) {

    function bf_b64dec($str)
    {
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base . $sixty_four, $str);
    }

}

$mainframe = Factory::getApplication();

// the engine renders outside the MVC dispatch (modules, plugins, iframes):
// make sure the component language is available for Text::_()
$mainframe->getLanguage()->load('com_breezingformsng');

$cache = Factory::getCache();
$cache->setCaching(false);

require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/functions/helpers.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/constants.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFRequest.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFFactory.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFIntegrate.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFPDF.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickMode.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickModeBootstrap.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickModeMobile.php');
require_once (JPATH_SITE . '/administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickModeOnePage.php');

// declare global variables
global
$database, // joomla database object
$ff_version, // FacileForms version number
$ff_config, // FacileForms configuration object
$ff_mospath, // path to root of joomla
$ff_compath, // path to component frontend root
$ff_mossite, // url of the site root
$ff_request, // array of request parameters ff_param_*
$ff_processor, // current form procesor object
$ff_target;    // index of form on current page

$database = $db = Factory::getContainer()->get(DatabaseInterface::class);

if (!isset($xModuleId)) {
    $xModuleId = 0;
}

if (!isset($ff_applic)) {
    $ff_applic = '';
}

// get paths
$ff_mospath = JPATH_SITE;
$ff_compath = $ff_mospath . '/components/com_breezingformsng';

// load config and initialize globals
require_once ($ff_compath . '/facileforms.class.php');
$ff_config = new facileFormsConf();
initFacileForms();

// context handed over by the including application (module/plugin or MVC template)
$bfEngineContext = [
    'ff_applic' => $ff_applic,
    'module_id' => $xModuleId,
    'params' => $params ?? null,
    'plg_editable' => $plg_editable ?? 0,
    'plg_editable_override' => $plg_editable_override ?? 0,
];

if (
    !BFRequest::getBool('bfCaptcha') &&
    !BFRequest::getBool('checkCaptcha') &&
    !BFRequest::getBool('confirmStripe') &&
    !BFRequest::getBool('confirmPayPal') &&
    !BFRequest::getBool('confirmPayPalIpn') &&
    !BFRequest::getBool('paypalDownload') &&
    !BFRequest::getBool('stripeDownload') &&
    !BFRequest::getBool('showPayPalConnectMsg') &&
    !BFRequest::getBool('successSofortueberweisung') &&
    !BFRequest::getBool('confirmSofortueberweisung') &&
    !BFRequest::getBool('sofortueberweisungDownload') &&
    !BFRequest::getBool('flashUpload') &&
    BFRequest::getVar('opt_in') != 'true' &&
    BFRequest::getVar('opt_out') != 'true'
) {
    (new FormRenderer())->render($bfEngineContext);
} else if (BFRequest::getBool('checkCaptcha')) {
    (new CaptchaCallback())->check();
} else if (BFRequest::getBool('confirmPayPalIpn') && $ff_applic == '') {
    (new PayPalCallback())->confirmIpn();
} else if (BFRequest::getBool('confirmStripe') && $ff_applic == '') {
    (new StripeCallback())->confirm();
} else if (BFRequest::getBool('stripeDownload') && $ff_applic == '') {
    (new StripeCallback())->download();
} else if (BFRequest::getBool('confirmPayPal') && $ff_applic == '') {
    (new PayPalCallback())->confirm();
} else if (BFRequest::getBool('paypalDownload') && $ff_applic == '') {
    (new PayPalCallback())->download();
} else if (BFRequest::getBool('showPayPalConnectMsg')) {
    (new PayPalCallback())->connectMessage();
} else if (BFRequest::getBool('successSofortueberweisung')) {
    (new SofortCallback())->success();
} else if (BFRequest::getBool('confirmSofortueberweisung')) {
    (new SofortCallback())->confirm();
} else if (BFRequest::getBool('sofortueberweisungDownload') && $ff_applic == '') {
    (new SofortCallback())->download();
} else if (BFRequest::getBool('flashUpload')) {
    (new FlashUploadCallback())->handle();
} else if (BFRequest::getVar('opt_in') == 'true') {
    (new OptCallback())->optIn();
} else if (BFRequest::getVar('opt_out') == 'true') {
    (new OptCallback())->optOut();
}

if (BFRequest::getBool('raw', false)) {
    session_write_close();
    exit;
}

$cache->setCaching(true);
