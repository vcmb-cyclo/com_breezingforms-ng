<?php

declare(strict_types=1);

$componentAutoload = __DIR__ . '/administrator/components/com_breezingformsng/vendor/autoload.php';

if (is_file($componentAutoload)) {
    require_once $componentAutoload;
}

\defined('_JEXEC') || \define('_JEXEC', 1);

foreach (
    [
        'JPATH_ROOT',
        'JPATH_SITE',
        'JPATH_BASE',
        'JPATH_ADMINISTRATOR',
        'JPATH_API',
        'JPATH_CLI',
        'JPATH_CONFIGURATION',
        'JPATH_CACHE',
        'JPATH_INSTALLATION',
        'JPATH_LIBRARIES',
        'JPATH_MANIFESTS',
        'JPATH_PLUGINS',
        'JPATH_PUBLIC',
        'JPATH_THEMES',
        'JPATH_COMPONENT',
        'JPATH_COMPONENT_SITE',
        'JPATH_COMPONENT_ADMINISTRATOR',
    ] as $bfPhpstanConstant
) {
    \defined($bfPhpstanConstant) || \define($bfPhpstanConstant, __DIR__);
}

\defined('JDEBUG') || \define('JDEBUG', false);
