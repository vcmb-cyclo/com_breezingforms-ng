<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service;

defined('_JEXEC') or die;

use Joomla\Input\Input;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\CaptchaCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\FlashUploadCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\OptCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\PayPalCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\SofortCallback;
use Vcmb\Component\BreezingformsNG\Site\Service\Callback\StripeCallback;

/**
 * Dispatches form rendering and the dedicated frontend callbacks.
 */
final class EngineDispatcher
{
    public function __construct(
        private readonly Input $input,
        private readonly FormRenderer $formRenderer,
        private readonly CaptchaCallback $captchaCallback,
        private readonly PayPalCallback $payPalCallback,
        private readonly StripeCallback $stripeCallback,
        private readonly SofortCallback $sofortCallback,
        private readonly FlashUploadCallback $flashUploadCallback,
        private readonly OptCallback $optCallback,
    ) {
    }

    public function dispatch(array $engineContext, string $application): void
    {
        if (!$this->isCallbackRequest()) {
            $this->formRenderer->render($engineContext);

            return;
        }

        if ($this->input->getBool('bfCaptcha', false)) {
            $this->captchaCallback->image();
        } elseif ($this->input->getBool('checkCaptcha', false)) {
            $this->captchaCallback->check();
        } elseif ($this->input->getBool('confirmPayPalIpn', false) && $application === '') {
            $this->payPalCallback->confirmIpn();
        } elseif ($this->input->getBool('confirmStripe', false) && $application === '') {
            $this->stripeCallback->confirm();
        } elseif ($this->input->getBool('stripeDownload', false) && $application === '') {
            $this->stripeCallback->download();
        } elseif ($this->input->getBool('confirmPayPal', false) && $application === '') {
            $this->payPalCallback->confirm();
        } elseif ($this->input->getBool('paypalDownload', false) && $application === '') {
            $this->payPalCallback->download();
        } elseif ($this->input->getBool('showPayPalConnectMsg', false)) {
            $this->payPalCallback->connectMessage();
        } elseif ($this->input->getBool('successSofortueberweisung', false)) {
            $this->sofortCallback->success();
        } elseif ($this->input->getBool('confirmSofortueberweisung', false)) {
            $this->sofortCallback->confirm();
        } elseif ($this->input->getBool('sofortueberweisungDownload', false) && $application === '') {
            $this->sofortCallback->download();
        } elseif ($this->input->getBool('flashUpload', false)) {
            $this->flashUploadCallback->handle();
        } elseif ($this->input->getString('opt_in', '') === 'true') {
            $this->optCallback->optIn();
        } elseif ($this->input->getString('opt_out', '') === 'true') {
            $this->optCallback->optOut();
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
}
