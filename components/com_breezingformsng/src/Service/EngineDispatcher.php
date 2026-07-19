<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\CaptchaCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\FlashUploadCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\OptCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\PayPalCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\SofortCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\StripeCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Support\RedirectHelper;
use Vcmb\Component\BreezingformsNG\Site\Service\Runtime\RequestParameterParser;

/**
 * Dispatches form rendering and the dedicated frontend callbacks.
 */
final class EngineDispatcher
{
    public function __construct(
        private readonly Input $input,
        private readonly CMSApplication $application,
        private readonly DatabaseInterface $database,
        private readonly MailerFactoryInterface $mailerFactory,
        private readonly CacheControllerFactoryInterface $cacheControllerFactory,
    ) {
    }

    public function dispatch(array $engineContext, string $application): void
    {
        if (!$this->isCallbackRequest()) {
            (new FormRenderer(
                $this->application,
                $this->database,
                $this->mailerFactory,
                $this->cacheControllerFactory,
                new RequestParameterParser(),
            ))->render($engineContext);

            return;
        }

        if ($this->input->getBool('checkCaptcha', false)) {
            (new CaptchaCallback($this->application))->check();
        } elseif ($this->input->getBool('confirmPayPalIpn', false) && $application === '') {
            (new PayPalCallback($this->application, $this->database, $this->redirectHelper()))->confirmIpn();
        } elseif ($this->input->getBool('confirmStripe', false) && $application === '') {
            (new StripeCallback($this->application, $this->database, $this->redirectHelper()))->confirm();
        } elseif ($this->input->getBool('stripeDownload', false) && $application === '') {
            (new StripeCallback($this->application, $this->database, $this->redirectHelper()))->download();
        } elseif ($this->input->getBool('confirmPayPal', false) && $application === '') {
            (new PayPalCallback($this->application, $this->database, $this->redirectHelper()))->confirm();
        } elseif ($this->input->getBool('paypalDownload', false) && $application === '') {
            (new PayPalCallback($this->application, $this->database, $this->redirectHelper()))->download();
        } elseif ($this->input->getBool('showPayPalConnectMsg', false)) {
            (new PayPalCallback($this->application, $this->database, $this->redirectHelper()))->connectMessage();
        } elseif ($this->input->getBool('successSofortueberweisung', false)) {
            (new SofortCallback($this->application, $this->database, $this->redirectHelper(), $this->mailerFactory))->success();
        } elseif ($this->input->getBool('confirmSofortueberweisung', false)) {
            (new SofortCallback($this->application, $this->database, $this->redirectHelper(), $this->mailerFactory))->confirm();
        } elseif ($this->input->getBool('sofortueberweisungDownload', false) && $application === '') {
            (new SofortCallback($this->application, $this->database, $this->redirectHelper(), $this->mailerFactory))->download();
        } elseif ($this->input->getBool('flashUpload', false)) {
            (new FlashUploadCallback($this->application, $this->database))->handle();
        } elseif ($this->input->getString('opt_in', '') === 'true') {
            (new OptCallback($this->application, $this->database))->optIn();
        } elseif ($this->input->getString('opt_out', '') === 'true') {
            (new OptCallback($this->application, $this->database))->optOut();
        }
    }

    private function isCallbackRequest(): bool
    {
        return $this->input->getBool('bfCaptcha', false)
            || $this->input->getBool('checkCaptcha', false)
            || $this->input->getBool('confirmStripe', false)
            || $this->input->getBool('confirmPayPal', false)
            || $this->input->getBool('confirmPayPalIpn', false)
            || $this->input->getBool('paypalDownload', false)
            || $this->input->getBool('stripeDownload', false)
            || $this->input->getBool('showPayPalConnectMsg', false)
            || $this->input->getBool('successSofortueberweisung', false)
            || $this->input->getBool('confirmSofortueberweisung', false)
            || $this->input->getBool('sofortueberweisungDownload', false)
            || $this->input->getBool('flashUpload', false)
            || $this->input->getString('opt_in', '') === 'true'
            || $this->input->getString('opt_out', '') === 'true';
    }

    private function redirectHelper(): RedirectHelper
    {
        return new RedirectHelper($this->application);
    }
}
