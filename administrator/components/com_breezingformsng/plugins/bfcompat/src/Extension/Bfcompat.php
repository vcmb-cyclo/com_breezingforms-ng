<?php

declare(strict_types=1);

namespace Vcmb\Plugin\System\Bfcompat\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

final class Bfcompat extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onAfterInitialise' => 'registerCompatibility'];
    }

    public function registerCompatibility(): void
    {
        CompatibilityLoader::register();
    }
}
