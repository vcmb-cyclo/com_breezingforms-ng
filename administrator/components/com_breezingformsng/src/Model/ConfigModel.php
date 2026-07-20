<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * Source history: admin/config.class.php (git mv — Phase 2).
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Migrates the legacy #__facileforms_config key/value store into the
 * component params (#__extensions.params), which com_config manages.
 */
class ConfigModel extends BaseDatabaseModel
{
    /**
     * Keys managed by config.xml; everything else in the legacy table is
     * runtime state of removed admin screens and is not carried over.
     */
    private const MANAGED_KEYS = [
        'disable_ip',
        'emailadr',
        'uploads',
        'csvdelimiter',
        'csvquote',
        'cellnewline',
    ];

    /**
     * Copy managed legacy config values into the component params.
     * A legacy value is only copied when the param is not set yet, so
     * values already saved through com_config are never overwritten.
     *
     * @return string[] the keys that were migrated
     */
    public function migrateFromLegacy(): array
    {
        $db = $this->getDatabase();

        try {
            $query = $db->createQuery()
                ->select([$db->quoteName('id'), $db->quoteName('value')])
                ->from($db->quoteName('#__facileforms_config'))
                ->whereIn($db->quoteName('id'), self::MANAGED_KEYS, ParameterType::STRING);
            $rows = $db->setQuery($query)->loadObjectList();
        } catch (\RuntimeException $e) {
            // Legacy table absent: nothing to migrate.
            return [];
        }

        if (!$rows) {
            return [];
        }

        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_breezingformsng'));
        $params = new Registry($db->setQuery($query)->loadResult());

        $migrated = [];

        foreach ($rows as $row) {
            if ($params->exists($row->id)) {
                continue;
            }

            $params->set($row->id, stripcslashes((string) $row->value));
            $migrated[] = $row->id;
        }

        if ($migrated === []) {
            return [];
        }

        $paramsJson = (string) $params;
        $query = $db->createQuery()
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_breezingformsng'))
            ->bind(':params', $paramsJson);
        $db->setQuery($query)->execute();

        return $migrated;
    }
}
