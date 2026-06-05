<?php
/**
 * @package     BreezingForms NG
 * @copyright   Copyright (C) 2024-2026 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Vcmb\Component\BreezingformsNG\Administrator\Extension\BreezingFormsNGComponent;
use Vcmb\Component\BreezingformsNG\Administrator\Helper\LegacyClassLoader;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $namespace = 'Vcmb\\Component\\BreezingformsNG';

        LegacyClassLoader::register();

        $container->registerServiceProvider(new MVCFactory($namespace));
        $container->registerServiceProvider(new ComponentDispatcherFactory($namespace));

        $container->set(
            ComponentInterface::class,
            static function (Container $container): ComponentInterface {
                $component = new BreezingFormsNGComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );

                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
