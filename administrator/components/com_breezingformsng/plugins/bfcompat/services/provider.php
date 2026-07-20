<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Vcmb\Plugin\System\Bfcompat\Extension\Bfcompat;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): PluginInterface {
                $plugin = new Bfcompat(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'bfcompat')
                );
                $plugin->setApplication($container->get(CMSApplicationInterface::class));

                return $plugin;
            }
        );
    }
};
