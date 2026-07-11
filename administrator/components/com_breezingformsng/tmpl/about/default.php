<?php
/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

if (!function_exists('bf_about_read_json_file')) {
    function bf_about_read_json_file($path)
    {
        if (!is_file($path)) {
            return array();
        }

        $jsonData = @file_get_contents($path);

        if (!is_string($jsonData) || $jsonData === '') {
            return array();
        }

        $decoded = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return array();
        }

        return $decoded;
    }
}

if (!function_exists('bf_about_get_version_information')) {
    function bf_about_get_version_information()
    {
        $versionInformation = array(
            'version' => '',
            'creationDate' => '',
            'author' => '',
            'copyright' => '',
            'license' => '',
        );

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_breezingformsng'));

            $db->setQuery($query);
            $manifestCache = (string) $db->loadResult();

            if ($manifestCache !== '') {
                $manifestData = json_decode($manifestCache, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($manifestData)) {
                    $versionInformation['version'] = (string) ($manifestData['version'] ?? '');
                    $versionInformation['creationDate'] = (string) ($manifestData['creationDate'] ?? '');
                    $versionInformation['author'] = (string) ($manifestData['author'] ?? '');
                    $versionInformation['copyright'] = (string) ($manifestData['copyright'] ?? '');
                    $versionInformation['license'] = (string) ($manifestData['license'] ?? '');
                }
            }
        } catch (Throwable $e) {
            // Fallback to local manifest files.
        }

        if ($versionInformation['version'] !== '') {
            return $versionInformation;
        }

        $manifestPaths = array(
            JPATH_ADMINISTRATOR . '/components/com_breezingformsng/com_breezingformsng.xml',
            JPATH_ADMINISTRATOR . '/components/com_breezingformsng/breezingforms.xml',
            JPATH_ADMINISTRATOR . '/components/com_breezingformsng/com_breezingformsng_ng.xml',
            JPATH_ADMINISTRATOR . '/components/com_breezingformsng/com_breezingformsng-ng.xml',
            JPATH_ADMINISTRATOR . '/components/com_breezingformsng/com_breezingformsng.xml',
            JPATH_ADMINISTRATOR . '/components/com_breezingformsng/com_breezingformsng.xml',
        );

        foreach ($manifestPaths as $manifestPath) {
            if (!is_file($manifestPath)) {
                continue;
            }

            $manifest = @simplexml_load_file($manifestPath);

            if (!$manifest instanceof SimpleXMLElement) {
                continue;
            }

            $versionInformation['version'] = (string) ($manifest->version ?? '');
            $versionInformation['creationDate'] = (string) ($manifest->creationDate ?? '');
            $versionInformation['author'] = (string) ($manifest->author ?? '');
            $versionInformation['copyright'] = (string) ($manifest->copyright ?? '');
            $versionInformation['license'] = (string) ($manifest->license ?? '');
            break;
        }

        return $versionInformation;
    }
}

if (!function_exists('bf_about_add_php_library')) {
    function bf_about_add_php_library(&$indexedLibraries, $name, $version, $isDev)
    {
        $name = trim((string) $name);
        $version = trim((string) $version);

        if ($name === '') {
            return;
        }

        if (!isset($indexedLibraries[$name])) {
            $indexedLibraries[$name] = array(
                'name' => $name,
                'version' => $version,
                'is_dev' => (bool) $isDev,
            );
            return;
        }

        if ($indexedLibraries[$name]['version'] === '' && $version !== '') {
            $indexedLibraries[$name]['version'] = $version;
        }

        $indexedLibraries[$name]['is_dev'] = (bool) $indexedLibraries[$name]['is_dev'] && (bool) $isDev;
    }
}

if (!function_exists('bf_about_collect_php_libraries_from_installed_json')) {
    function bf_about_collect_php_libraries_from_installed_json(&$indexedLibraries, $installedJsonPath)
    {
        $installedData = bf_about_read_json_file($installedJsonPath);

        if (!$installedData) {
            return;
        }

        $packages = array();

        if (isset($installedData['packages']) && is_array($installedData['packages'])) {
            $packages = $installedData['packages'];
        } elseif (is_array($installedData)) {
            $packages = $installedData;
        }

        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }

            $name = (string) ($package['name'] ?? '');
            $version = (string) ($package['pretty_version'] ?? $package['version'] ?? '');
            $isDev = (bool) ($package['dev_requirement'] ?? false);

            bf_about_add_php_library($indexedLibraries, $name, $version, $isDev);
        }
    }
}

if (!function_exists('bf_about_collect_php_library_from_composer_json')) {
    function bf_about_collect_php_library_from_composer_json(&$indexedLibraries, $composerJsonPath)
    {
        $composerData = bf_about_read_json_file($composerJsonPath);

        if (!$composerData) {
            return;
        }

        $name = (string) ($composerData['name'] ?? '');
        $version = (string) ($composerData['version'] ?? '');
        $isDev = false;

        bf_about_add_php_library($indexedLibraries, $name, $version, $isDev);
    }
}

if (!function_exists('bf_about_get_php_libraries')) {
    function bf_about_get_php_libraries()
    {
        $indexedLibraries = array();

        $stripeInstalled = JPATH_ADMINISTRATOR . '/components/com_breezingformsng/administrator/libraries/stripe/vendor/composer/installed.json';
        $dropboxInstalled = JPATH_ADMINISTRATOR . '/components/com_breezingformsng/administrator/libraries/dropbox/v2/composer/installed.json';
        $tcpdfComposer = JPATH_ADMINISTRATOR . '/components/com_breezingformsng/administrator/libraries/tcpdf/composer.json';
        $vendorComposer = JPATH_ADMINISTRATOR . '/components/com_breezingformsng/administrator/libraries/vendor/composer.json';

        bf_about_collect_php_libraries_from_installed_json($indexedLibraries, $stripeInstalled);
        bf_about_collect_php_libraries_from_installed_json($indexedLibraries, $dropboxInstalled);
        bf_about_collect_php_library_from_composer_json($indexedLibraries, $tcpdfComposer);

        if (empty($indexedLibraries)) {
            bf_about_collect_php_library_from_composer_json($indexedLibraries, $vendorComposer);
        }

        $libraries = array_values($indexedLibraries);

        usort($libraries, function ($a, $b) {
            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        return $libraries;
    }
}

if (!function_exists('bf_about_extract_version_from_file')) {
    function bf_about_extract_version_from_file($filePath)
    {
        if (!is_file($filePath)) {
            return '';
        }

        $contents = @file_get_contents($filePath, false, null, 0, 8192);

        if (!is_string($contents) || $contents === '') {
            return '';
        }

        $patterns = array(
            '/Inline Form Validation Engine\s+([0-9A-Za-z\.\-]+)/i',
            '/jsTree\s+([0-9A-Za-z\.\-]+)/i',
            '/JQuery\s+([0-9A-Za-z\.\-]+)/i',
            '/version\s*[:=]\s*[\'"]([0-9A-Za-z\.\-]+)/i',
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $contents, $matches)) {
                return trim((string) ($matches[1] ?? ''));
            }
        }

        return '';
    }
}

if (!function_exists('bf_about_get_javascript_libraries')) {
    function bf_about_get_javascript_libraries()
    {
        $notAvailable = BFText::_('COM_BREEZINGFORMSNG_NOT_AVAILABLE');
        $bundledSource = BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARY_SOURCE_BUNDLED');
        $basePath = JPATH_ADMINISTRATOR . '/components/com_breezingformsng/administrator/libraries/jquery/';
        $libraries = array();

        $candidates = array(
            array(
                'name' => 'jQuery',
                'script_path' => $basePath . 'jq.js',
                'css_path' => '',
            ),
            array(
                'name' => 'jQuery UI',
                'script_path' => $basePath . 'jq-ui.min.js',
                'css_path' => '',
            ),
            array(
                'name' => 'jsTree',
                'script_path' => $basePath . 'jtree/tree_component.min.js',
                'css_path' => $basePath . 'jtree/tree_component.css',
            ),
            array(
                'name' => 'ValidationEngine',
                'script_path' => $basePath . 'jquery.validationEngine.js',
                'css_path' => $basePath . 'validationEngine.jquery.css',
            ),
        );

        foreach ($candidates as $candidate) {
            $scriptPath = (string) ($candidate['script_path'] ?? '');

            if ($scriptPath === '' || !is_file($scriptPath)) {
                continue;
            }

            $version = bf_about_extract_version_from_file($scriptPath);
            $assets = 'JS';
            $cssPath = (string) ($candidate['css_path'] ?? '');

            if ($cssPath !== '' && is_file($cssPath)) {
                $assets = 'JS + CSS';
            }

            $libraries[] = array(
                'name' => (string) ($candidate['name'] ?? ''),
                'version' => $version !== '' ? $version : $notAvailable,
                'assets' => $assets,
                'source' => $bundledSource,
            );
        }

        usort($libraries, function ($a, $b) {
            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        return $libraries;
    }
}

$versionInformation = bf_about_get_version_information();
$phpLibraries = bf_about_get_php_libraries();
$javascriptLibraries = bf_about_get_javascript_libraries();
$logReport = is_array($this->logReport ?? null) ? $this->logReport : array();
$notAvailable = BFText::_('COM_BREEZINGFORMSNG_NOT_AVAILABLE');

$versionValue = $versionInformation['version'] !== '' ? $versionInformation['version'] : $notAvailable;
$creationDateValue = $versionInformation['creationDate'] !== '' ? $versionInformation['creationDate'] : $notAvailable;
$authorValue = $versionInformation['author'] !== '' ? $versionInformation['author'] : $notAvailable;
$copyrightValue = $versionInformation['copyright'] !== '' ? $versionInformation['copyright'] : $notAvailable;
$licenseValue = trim((string) ($versionInformation['license'] ?? ''));
$genericLicenseValues = array('gpl', 'gnu/gpl', 'gnu/gpl v2 or later');

if ($licenseValue === '' || in_array(strtolower($licenseValue), $genericLicenseValues, true)) {
    $licenseValue = BFText::_('COM_BREEZINGFORMSNG_LICENSE_FALLBACK');
}

$licenseUrl = 'https://www.gnu.org/licenses/gpl-2.0.html';
$vcmbUrl = 'https://breezingforms-ng.vcmb.fr';
$githubUrl = 'https://github.com/vcmb-cyclo/com_breezingformsng';
$logFileName = (string) ($logReport['file'] ?? $notAvailable);
$logSize = (int) ($logReport['size'] ?? 0);
$logLoadedAt = (string) ($logReport['loaded_at'] ?? $notAvailable);
$logContent = (string) ($logReport['content'] ?? '');
$logDisplayContent = $logContent;

if ($logDisplayContent !== '') {
    $logLines = preg_split('/\r\n|\r|\n/', $logDisplayContent);

    if (is_array($logLines) && $logLines !== array()) {
        $logDisplayContent = implode(PHP_EOL, array_reverse($logLines));
    }
}

$logTruncated = (int) ($logReport['truncated'] ?? 0) === 1;
$logTailBytes = (int) ($logReport['tail_bytes'] ?? 0);
$aboutDescription = (string) BFText::_('COM_BREEZINGFORMSNG_ABOUT_DESC');
$aboutDescription = str_replace(
    '<strong>BreezingForms</strong>',
    '<strong>BreezingForms NG</strong>',
    $aboutDescription
);
$aboutDescription = str_replace(
    'GPL-2.0-or-later',
    '<a href="https://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank" rel="noopener noreferrer">GPL-2.0-or-later</a>',
    $aboutDescription
);
$aboutDescription = str_replace(
    'GPL-2.0+',
    '<a href="https://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank" rel="noopener noreferrer">GPL-2.0+</a>',
    $aboutDescription
);
?>
<?php Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('com_breezingformsng.about-style'); ?>

<form action="index.php?option=com_breezingformsng&task=about.display&amp;view=about" method="post" name="adminForm" id="adminForm">
    <div class="bf-about-intro mt-3 mb-3">
        <div class="bf-about-intro-media">
            <img
                src="<?php echo htmlspecialchars(Uri::root(true) . '/media/com_breezingformsng/images/bf_logo.png', ENT_QUOTES, 'UTF-8'); ?>"
                alt="<?php echo htmlspecialchars(BFText::_('COM_BREEZINGFORMSNG_ABOUT'), ENT_QUOTES, 'UTF-8'); ?>"
                class="img-fluid"
                style="max-width: 150px; height: auto;"
                loading="lazy"
            />
        </div>
        <div class="bf-about-intro-content">
            <p class="mb-0">
                <?php echo $aboutDescription; ?>
            </p>
            <div class="bf-about-intro-links">
                <a class="bf-about-intro-link bf-about-intro-link--vcmb" href="<?php echo htmlspecialchars($vcmbUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo BFText::_('COM_BREEZINGFORMSNG_VCMB_LINK'); ?></a>
                <a class="bf-about-intro-link bf-about-intro-link--github" href="<?php echo htmlspecialchars($githubUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo BFText::_('COM_BREEZINGFORMSNG_GITHUB_LINK'); ?></a>
                <a class="bf-about-intro-link bf-about-intro-link--license" href="<?php echo htmlspecialchars($licenseUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo BFText::_('COM_BREEZINGFORMSNG_LICENSE_LINK'); ?></a>
                <a class="bf-about-intro-link bf-about-intro-link--log" href="#bf-about-log"><?php echo BFText::_('COM_BREEZINGFORMSNG_ABOUT_SHOW_LOG'); ?></a>
            </div>
        </div>
    </div>

    <div class="card mt-3 bf-about-version-card">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 bf-about-version-header">
                <h3 class="h5 mb-0 bf-about-version-title"><?php echo BFText::_('COM_BREEZINGFORMSNG_VERSION_INFORMATION'); ?></h3>
                <span class="bf-about-version-badge">BreezingForms</span>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="bf-about-version-tile bf-about-version-tile--version">
                        <span class="bf-about-version-icon" aria-hidden="true">VER</span>
                        <p class="bf-about-version-label"><?php echo BFText::_('COM_BREEZINGFORMSNG_VERSION_LABEL'); ?></p>
                        <p class="bf-about-version-value"><?php echo htmlspecialchars((string) $versionValue, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="bf-about-version-tile bf-about-version-tile--date">
                        <span class="bf-about-version-icon" aria-hidden="true">DATE</span>
                        <p class="bf-about-version-label"><?php echo BFText::_('COM_BREEZINGFORMSNG_CREATION_DATE_LABEL'); ?></p>
                        <p class="bf-about-version-value"><?php echo htmlspecialchars((string) $creationDateValue, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="bf-about-version-tile bf-about-version-tile--author">
                        <span class="bf-about-version-icon" aria-hidden="true">DEV</span>
                        <p class="bf-about-version-label"><?php echo BFText::_('COM_BREEZINGFORMSNG_AUTHOR_LABEL'); ?></p>
                        <p class="bf-about-version-value"><?php echo htmlspecialchars((string) $authorValue, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="bf-about-version-label mt-2"><?php echo BFText::_('COM_BREEZINGFORMSNG_COPYRIGHT_LABEL'); ?></p>
                        <p class="bf-about-version-value"><?php echo htmlspecialchars((string) $copyrightValue, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-12 col-lg-4">
                    <div class="bf-about-version-tile bf-about-version-tile--license">
                        <span class="bf-about-version-icon" aria-hidden="true">GPL</span>
                        <p class="bf-about-version-label"><?php echo BFText::_('COM_BREEZINGFORMSNG_LICENSE_LABEL'); ?></p>
                        <p class="bf-about-version-value"><?php echo htmlspecialchars((string) $licenseValue, ENT_QUOTES, 'UTF-8'); ?></p>
                        <a class="bf-about-version-link" href="<?php echo htmlspecialchars($licenseUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo BFText::_('COM_BREEZINGFORMSNG_LICENSE_LINK'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3" id="bf-about-log">
        <div class="card-body">
            <h3 class="h6 card-title mb-3"><?php echo BFText::_('COM_BREEZINGFORMSNG_ABOUT_LOG_TITLE'); ?></h3>
            <p class="text-muted small mb-2">
                <?php echo sprintf(
                    BFText::_('COM_BREEZINGFORMSNG_ABOUT_LOG_LAST_READ'),
                    htmlspecialchars($logFileName, ENT_QUOTES, 'UTF-8'),
                    number_format($logSize, 0, '.', ' '),
                    htmlspecialchars($logLoadedAt, ENT_QUOTES, 'UTF-8')
                ); ?>
            </p>

            <?php if ($logTruncated) : ?>
                <div class="alert alert-warning py-2">
                    <?php echo sprintf(BFText::_('COM_BREEZINGFORMSNG_ABOUT_LOG_TRUNCATED'), max(1, $logTailBytes)); ?>
                </div>
            <?php endif; ?>

            <?php if ($logDisplayContent === '') : ?>
                <div class="alert alert-info mb-0">
                    <?php echo BFText::_('COM_BREEZINGFORMSNG_ABOUT_LOG_EMPTY'); ?>
                </div>
            <?php else : ?>
                <pre class="bg-body-tertiary text-body p-3 border rounded small mb-0" style="max-height: 420px; overflow: auto;"><?php echo htmlspecialchars($logDisplayContent, ENT_QUOTES, 'UTF-8'); ?></pre>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h3 class="h6 card-title mb-3"><?php echo BFText::_('COM_BREEZINGFORMSNG_PHP_LIBRARIES'); ?></h3>
            <?php if (empty($phpLibraries)) : ?>
                <div class="alert alert-info mb-0">
                    <?php echo BFText::_('COM_BREEZINGFORMSNG_PHP_LIBRARIES_NOT_AVAILABLE'); ?>
                </div>
            <?php else : ?>
                <p class="text-muted small">
                    <?php echo sprintf(BFText::_('COM_BREEZINGFORMSNG_PHP_LIBRARIES_COUNT'), count($phpLibraries)); ?>
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_PHP_LIBRARY'); ?></th>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_PHP_LIBRARY_VERSION'); ?></th>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_PHP_LIBRARY_SCOPE'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($phpLibraries as $library) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($library['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) (($library['version'] ?? '') !== '' ? $library['version'] : $notAvailable), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo BFText::_(!empty($library['is_dev']) ? 'COM_BREEZINGFORMSNG_PHP_LIBRARY_SCOPE_DEV' : 'COM_BREEZINGFORMSNG_PHP_LIBRARY_SCOPE_RUNTIME'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h3 class="h6 card-title mb-3"><?php echo BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARIES'); ?></h3>
            <?php if (empty($javascriptLibraries)) : ?>
                <div class="alert alert-info mb-0">
                    <?php echo BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARIES_NOT_AVAILABLE'); ?>
                </div>
            <?php else : ?>
                <p class="text-muted small">
                    <?php echo sprintf(BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARIES_COUNT'), count($javascriptLibraries)); ?>
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARY'); ?></th>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARY_VERSION'); ?></th>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARY_ASSETS'); ?></th>
                            <th scope="col"><?php echo BFText::_('COM_BREEZINGFORMSNG_JS_LIBRARY_SOURCE'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($javascriptLibraries as $library) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($library['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($library['version'] ?? $notAvailable), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($library['assets'] ?? $notAvailable), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($library['source'] ?? $notAvailable), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <input type="hidden" name="option" value="com_breezingformsng" />
    <input type="hidden" name="task" value="about.display" />
    <input type="hidden" name="view" value="about" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
