<?php
/**
 * @package     BreezingForms NG
 * @copyright   Copyright (C) 2024-2026 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Helper;

\defined('_JEXEC') or die;

use Vcmb\Component\BreezingformsNG\Administrator\Table\ElementTable;

final class LegacyClassLoader
{
    private const CLASS_PATHS = [
        'BFBehaviorTabs' => 'libraries/crosstec/classes/BFBehaviorTabs.php',
        'BFFactory' => 'libraries/crosstec/classes/BFFactory.php',
        'BFIntegrate' => 'libraries/crosstec/classes/BFIntegrate.php',
        'BFJNewTabs' => 'libraries/crosstec/classes/BFJNewTabs.php',
        'BFJoomlaConfig' => 'libraries/crosstec/classes/BFJoomlaConfig.php',
        'BFPDF' => 'libraries/crosstec/classes/BFPDF.php',
        'BFPagination' => 'libraries/crosstec/classes/BFPagination.php',
        'BFPaginationChrome' => 'libraries/crosstec/classes/BFPaginationChrome.php',
        'BFQuickMode' => 'libraries/crosstec/classes/BFQuickMode.php',
        'BFQuickModeBootstrap' => 'libraries/crosstec/classes/BFQuickModeBootstrap.php',
        'BFQuickModeMobile' => 'libraries/crosstec/classes/BFQuickModeMobile.php',
        'BFQuickModeOnePage' => 'libraries/crosstec/classes/BFQuickModeOnePage.php',
        'BFRequest' => 'libraries/crosstec/classes/BFRequest.php',
        'BFTabs' => 'libraries/crosstec/classes/BFTabs.php',
        'BFText' => 'libraries/crosstec/classes/BFText.php',
    ];

    private const CLASS_ALIASES = [
        'BFTableElements' => ElementTable::class,
    ];

    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        spl_autoload_register([self::class, 'load']);

        self::$registered = true;
    }

    public static function load(string $class): void
    {
        if (isset(self::CLASS_ALIASES[$class])) {
            class_alias(self::CLASS_ALIASES[$class], $class);

            return;
        }

        if (!isset(self::CLASS_PATHS[$class])) {
            return;
        }

        require JPATH_ADMINISTRATOR . '/components/com_breezingformsng/' . self::CLASS_PATHS[$class];
    }
}
