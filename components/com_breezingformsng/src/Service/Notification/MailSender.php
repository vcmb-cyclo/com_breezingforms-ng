<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Notification;

\defined('_JEXEC') or die;

use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\Registry\Registry;

final class MailSender
{
    public function __construct(
        private readonly MailerFactoryInterface $mailerFactory,
        private readonly Registry $configuration
    ) {
    }

    /**
     * @param list<string> $recipients
     * @param list<string> $attachments
     * @param list<string> $cc
     * @param list<string> $bcc
     */
    public function send(
        string $from,
        string $fromName,
        array $recipients,
        string $subject,
        string $body,
        array $attachments = [],
        ?bool $html = null,
        array $cc = [],
        array $bcc = [],
        string $alternativeSender = ''
    ): void {
        /** @var Mail $mailer */
        $mailer = $this->mailerFactory->createMailer();
        $defaultAddress = (string) $this->configuration->get('mailfrom', '');
        $defaultName = (string) $this->configuration->get('fromname', '');
        $senderAddress = $alternativeSender !== '' ? $alternativeSender : $defaultAddress;
        $senderName = $fromName !== '' ? $fromName : $defaultName;

        $mailer->setSender($senderAddress, $senderName);
        $mailer->setSubject($subject);
        $mailer->setBody($body);

        if ($from !== '' && $from !== $senderAddress) {
            $mailer->addReplyTo($from, $senderName);
        }

        foreach ($recipients as $recipient) {
            $mailer->addRecipient($recipient);
        }

        foreach ($attachments as $attachment) {
            if (trim($attachment) !== '') {
                $mailer->addAttachment($attachment);
            }
        }

        if ($html !== null) {
            $mailer->isHtml($html);
        }

        foreach ($cc as $recipient) {
            $mailer->addCc($recipient);
        }

        foreach ($bcc as $recipient) {
            $mailer->addBcc($recipient);
        }

        $mailer->send();
    }
}
