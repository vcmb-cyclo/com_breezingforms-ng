<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Source history: admin/quickmode.class.php (git mv — Phase 7).
 */

namespace Vcmb\Component\BreezingformsNG\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

class QuickmodeModel extends BaseModel
{
    private DatabaseInterface $db;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    public function save(int $form, array $dataObject): int
    {
        $areas = new \stdClass();
        $areas->container    = [];
        $areas->container[0] = ['elements' => [], 'elementCount' => 0];

        $this->createAreasFromTree($dataObject, $areas);

        // Inject submit-related fake elements from published scripts.
        $fakes = ['ff_validate_submit', 'ff_resetForm', 'ff_validate_prevpage', 'ff_validate_nextpage'];
        foreach ($fakes as $idx => $scriptName) {
            $this->db->setQuery(
                'SELECT id FROM #__facileforms_scripts WHERE published=1 AND name = ' . $this->db->quote($scriptName) .
                ' ORDER BY type, title, name, id DESC'
            );
            $rows = $this->db->loadObjectList();
            if (!empty($rows)) {
                $n  = $idx === 0 ? '' : (string) ($idx + 1);
                $el = $this->getDefaultElement();
                $el['title']       = 'bfFakeTitle' . $n;
                $el['name']        = 'bfFakeName' . $n;
                $el['logging']     = 0;
                $el['script2cond'] = 1;
                $el['script2id']   = (int) $rows[0]->id;
                $areas->container[0]['elements'][] = $el;
            }
        }

        $mdata = $dataObject['properties'];

        return $this->save2(
            $form,
            (string) ($mdata['name'] ?? ''),
            (string) ($mdata['title'] ?? ''),
            (string) ($mdata['description'] ?? ''),
            base64_encode((string) json_encode($dataObject)),
            $areas->container,
            count($dataObject['children'] ?? [])
        );
    }

    public function getFormOptions(int $form): ?\stdClass
    {
        $this->db->setQuery(
            'SELECT package, name, title, description, emailntf, emailadr FROM #__facileforms_forms WHERE id = ' .
            $this->db->quote($form)
        );
        $list = $this->db->loadObjectList();

        return count($list) === 1 ? $list[0] : null;
    }

    public function getTemplateCode(int $form): string
    {
        $this->db->setQuery(
            'SELECT template_code FROM #__facileforms_forms WHERE id = ' . $this->db->quote($form)
        );
        $list = $this->db->loadObjectList();

        return count($list) === 1 ? (string) base64_decode($list[0]->template_code) : '';
    }

    public function getElementScripts(): array
    {
        $result = [];

        foreach (['Element Validation' => 'validation', 'Element Action' => 'action', 'Element Init' => 'init'] as $type => $key) {
            $this->db->setQuery(
                'SELECT id, package, name, title, description, type FROM #__facileforms_scripts' .
                ' WHERE published = 1 AND type = ' . $this->db->quote($type)
            );
            $result[$key] = $this->db->loadObjectList();
        }

        return $result;
    }

    public function getThemes(): array
    {
        return $this->scanThemeDir(JPATH_SITE . '/media/breezingforms/themes/');
    }

    public function getThemesBootstrap(): array
    {
        return $this->scanThemeDir(JPATH_SITE . '/media/breezingforms/themes-bootstrap4/');
    }

    public function getThemesBootstrap4(): array
    {
        return $this->scanThemeDir(JPATH_SITE . '/media/breezingforms/themes-bootstrap4/');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function scanThemeDir(string $folder): array
    {
        $themes = [];

        if ($handle = @opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, ['.', '..', 'images', 'img', '.csv', '.svn'], true) && is_dir($folder . $file)) {
                    $themes[] = $file;
                }
            }
            closedir($handle);
        }

        return $themes;
    }

    private function getDefaultElement(): array
    {
        return [
            'element'             => null,
            'bfType'              => '',
            'elementType'         => '',
            'options'             => [],
            'data1'               => '',
            'data2'               => '',
            'data3'               => '',
            'script1cond'         => 0,
            'script1id'           => 0,
            'script1code'         => '',
            'script1flag1'        => 0,
            'script1flag2'        => 0,
            'script2cond'         => 0,
            'script2id'           => 0,
            'script2code'         => '',
            'script2flag1'        => 0,
            'script2flag2'        => 0,
            'script2flag3'        => 0,
            'script2flag4'        => 0,
            'script2flag5'        => 0,
            'script3cond'         => 0,
            'script3id'           => 0,
            'script3code'         => '',
            'script3msg'          => '',
            'functionNameScript1' => '',
            'functionNameScript2' => '',
            'functionNameScript3' => '',
            'flag1'               => 0,
            'flag2'               => 0,
            'mailback'            => 0,
            'mailbackfile'        => '',
            'title'               => '',
            'name'                => '',
            'page'                => 1,
            'orderNumber'         => 0,
            'dbId'                => 0,
            'appElementOrderId'   => 0,
            'id'                  => 0,
            'logging'             => 1,
            'qId'                 => 0,
            'internalType'        => '',
        ];
    }

    private function createAreasFromTree(array $dataObject, \stdClass $areas, int $page = 1): void
    {
        $element = $this->getDefaultElement();

        if (isset($dataObject['attributes'], $dataObject['properties'])) {
            $mdata = $dataObject['properties'];

            if ($mdata['type'] === 'root') {
                if (!empty($mdata['themebootstrap']) && !empty($mdata['themebootstrapvars'])
                    && isset($mdata['themebootstrapbefore'])
                    && $mdata['themebootstrapbefore'] === $mdata['themebootstrap']
                ) {
                    $folder   = (!empty($mdata['themebootstrapUse3'])) ? 'themes-bootstrap3' : 'themes-bootstrap';
                    $varspath = JPATH_SITE . '/media/breezingforms/' . $folder . '/' . $mdata['themebootstrap'] . '/vars.txt';
                    if (file_exists($varspath)) {
                        File::write($varspath, $mdata['themebootstrapvars']);
                    }
                }
            } elseif ($mdata['type'] === 'page') {
                $ex   = explode('bfQuickModePage', $dataObject['attributes']['id']);
                $page = (int) ($ex[1] ?? 1);
            } elseif ($mdata['type'] === 'element') {
                $element['internalType'] = $mdata['bfType'];

                switch ($mdata['bfType']) {
                    case 'bfTextfield':
                        $element['bfType']             = 'Text';
                        $element['options']['value']    = $mdata['value'];
                        $element['options']['placeholder'] = $mdata['placeholder'] ?? '';
                        $element['data1']              = $mdata['value'];
                        $element['options']['password'] = $mdata['password'];
                        $element['flag1']              = $mdata['password'] ? 1 : 0;
                        $element['options']['mailback'] = $mdata['mailback'];
                        $element['mailback']           = $mdata['mailback'] ? 1 : 0;
                        $element['mailbackAsSender']   = $mdata['mailbackAsSender'] ? 1 : 0;
                        $element['mailbackfile']       = $mdata['mailbackfile'];
                        break;
                    case 'bfTextarea':
                        $element['bfType']             = 'Textarea';
                        $element['options']['value']   = $mdata['value'];
                        $element['data1']              = $mdata['value'];
                        $element['options']['placeholder'] = $mdata['placeholder'] ?? '';
                        break;
                    case 'bfSelect':
                        $element['bfType']             = 'Select List';
                        $element['options']['multiple'] = $mdata['multiple'];
                        $element['options']['options']  = $mdata['list'];
                        $element['options']['mailback'] = $mdata['mailback'];
                        $element['mailback']           = $mdata['mailback'] ? 1 : 0;
                        $element['data1']              = 1;
                        $element['data2']              = $mdata['list'];
                        $element['flag1']              = $mdata['multiple'] ? 1 : 0;
                        break;
                    case 'bfRadioGroup':
                        $element['bfType'] = 'Radio Group';
                        $element['data2']  = $mdata['group'];
                        break;
                    case 'bfCheckboxGroup':
                        $element['bfType'] = 'Checkbox Group';
                        $element['data2']  = $mdata['group'];
                        break;
                    case 'bfSignature':
                        $element['bfType'] = 'Signature';
                        break;
                    case 'bfCheckbox':
                        $element['bfType']                   = 'Checkbox';
                        $element['options']['checked']        = $mdata['checked'];
                        $element['flag1']                    = $mdata['checked'] ? 1 : 0;
                        $element['options']['value']         = $mdata['value'];
                        $element['data1']                    = $mdata['value'];
                        $element['mailbackAccept']           = $mdata['mailbackAccept'];
                        $element['mailbackAcceptConnectWith'] = $mdata['mailbackConnectWith'];
                        break;
                    case 'bfFile':
                        $element['bfType']                          = 'File Upload';
                        $element['options']['allowedFileExtensions'] = strtolower($mdata['allowedFileExtensions']);
                        $element['options']['timestamp']             = $mdata['timestamp'];
                        $element['options']['useUrl']                = $mdata['useUrl'] ?? false;
                        $element['options']['html5']                 = $mdata['html5'] ?? false;
                        $element['options']['useUrlDownloadDirectory'] = $mdata['useUrlDownloadDirectory'] ?? false;
                        $element['options']['resize_target_width']   = $mdata['resize_target_width'] ?? '';
                        $element['options']['resize_target_height']  = $mdata['resize_target_height'] ?? '';
                        $element['options']['resize_type']           = $mdata['resize_type'] ?? '';
                        $element['options']['resize_bgcolor']        = $mdata['resize_bgcolor'] ?? '';
                        $element['flag1']                           = $mdata['timestamp'] ? 1 : 0;
                        $element['options']['uploadDirectory']       = $mdata['uploadDirectory'];
                        $element['data1']                           = $mdata['uploadDirectory'];
                        $element['data2']                           = strtolower($mdata['allowedFileExtensions']);
                        $element['options']['attachToAdminMail']     = $mdata['attachToAdminMail'];
                        $element['options']['attachToUserMail']      = $mdata['attachToUserMail'];
                        break;
                    case 'bfSubmitButton':
                        $element['bfType']             = 'Regular Button';
                        $element['options']['value']    = $mdata['value'];
                        $element['options']['readonly'] = false;
                        $element['data1']              = $mdata['value'];
                        break;
                    case 'bfHidden':
                        $element['bfType'] = 'Hidden Input';
                        $element['data1']  = $mdata['value'];
                        break;
                    case 'Summarize':
                        $element['bfType'] = 'Summarize';
                        break;
                    case 'bfCaptcha':
                        $element['bfType'] = 'Captcha';
                        $element['width']  = isset($mdata['width']) ? (int) $mdata['width'] : 230;
                        break;
                    case 'bfNumberInput':
                        $element['bfType'] = 'Number Input';
                        $element['data1']  = $mdata['value'];
                        break;
                    case 'bfReCaptcha':
                        $element['bfType']   = 'ReCaptcha';
                        $element['pubkey']   = $mdata['pubkey'];
                        $element['privkey']  = $mdata['privkey'];
                        $element['theme']    = $mdata['theme'];
                        break;
                    case 'bfCalendar':
                    case 'bfCalendarResponsive':
                        $element['bfType'] = 'Calendar';
                        $element['data1']  = $mdata['value'];
                        break;
                    case 'bfPayPal':
                        $element['bfType']                              = 'PayPal';
                        $element['options']['testaccount']               = $mdata['testaccount'];
                        $element['options']['useIpn']                    = $mdata['useIpn'] ?? false;
                        $element['options']['downloadableFile']          = $mdata['downloadableFile'];
                        $element['options']['filepath']                  = $mdata['filepath'];
                        $element['options']['downloadTries']             = $mdata['downloadTries'];
                        $element['options']['business']                  = $mdata['business'];
                        $element['options']['token']                     = $mdata['token'];
                        $element['options']['testBusiness']              = $mdata['testBusiness'];
                        $element['options']['testToken']                 = $mdata['testToken'];
                        $element['options']['itemname']                  = $mdata['itemname'];
                        $element['options']['itemnumber']                = $mdata['itemnumber'];
                        $element['options']['amount']                    = $mdata['amount'];
                        $element['options']['tax']                       = $mdata['tax'];
                        $element['options']['thankYouPage']              = $mdata['thankYouPage'];
                        $element['options']['cancelURL']                 = $mdata['cancelURL'] ?? '';
                        $element['options']['locale']                    = $mdata['locale'];
                        $element['options']['currencyCode']              = $mdata['currencyCode'];
                        $element['options']['image']                     = $mdata['image'];
                        $element['options']['sendNotificationAfterPayment'] = $mdata['sendNotificationAfterPayment'];
                        $element['data1']                                = $mdata['image'];
                        break;
                    case 'bfStripe':
                        $element['bfType']                              = 'Stripe';
                        $element['options']['downloadableFile']          = $mdata['downloadableFile'];
                        $element['options']['filepath']                  = $mdata['filepath'];
                        $element['options']['downloadTries']             = $mdata['downloadTries'];
                        $element['options']['secretKey']                 = $mdata['secretKey'];
                        $element['options']['publishableKey']            = $mdata['publishableKey'];
                        $element['options']['itemname']                  = $mdata['itemname'];
                        $element['options']['amount']                    = $mdata['amount'];
                        $element['options']['thankYouPage']              = $mdata['thankYouPage'];
                        $element['options']['currencyCode']              = $mdata['currencyCode'];
                        $element['options']['sendNotificationAfterPayment'] = $mdata['sendNotificationAfterPayment'];
                        $element['options']['image']                     = $mdata['image'];
                        $element['options']['emailfield']                = $mdata['emailfield'] ?? '';
                        $element['data1']                                = $mdata['image'];
                        break;
                    case 'bfSofortueberweisung':
                        $element['bfType']                              = 'Sofortueberweisung';
                        $element['options']['mailback']                  = $mdata['mailback'];
                        $element['options']['downloadableFile']          = $mdata['downloadableFile'];
                        $element['options']['filepath']                  = $mdata['filepath'];
                        $element['options']['downloadTries']             = $mdata['downloadTries'];
                        $element['options']['user_id']                   = $mdata['user_id'];
                        $element['options']['project_id']                = $mdata['project_id'];
                        $element['options']['project_password']          = $mdata['project_password'];
                        $element['options']['reason_1']                  = $mdata['reason_1'];
                        $element['options']['reason_2']                  = $mdata['reason_2'];
                        $element['options']['amount']                    = $mdata['amount'];
                        $element['options']['thankYouPage']              = $mdata['thankYouPage'];
                        $element['options']['language_id']               = $mdata['language_id'];
                        $element['options']['currency_id']               = $mdata['currency_id'];
                        $element['options']['image']                     = $mdata['image'];
                        $element['options']['sendNotificationAfterPayment'] = $mdata['sendNotificationAfterPayment'] ?? false;
                        $element['data1']                                = $mdata['image'];
                        break;
                    default:
                        $element['bfType'] = 'Unknown';
                }

                $areas->container[0]['elementCount']++;

                $element['title']               = $mdata['label'];
                $element['name']                = $mdata['bfName'];
                $element['orderNumber']         = $mdata['orderNumber'] != -1 ? $mdata['orderNumber'] : $areas->container[0]['elementCount'];
                $element['tabIndex']            = $mdata['tabIndex'];
                $element['logging']             = $mdata['logging'];
                $element['options']['readonly'] = $mdata['readonly'] ?? false;
                $element['flag2']               = !empty($mdata['readonly']) ? 1 : 0;
                // Validation
                $element['script3id']            = $mdata['validationId'];
                $element['script3code']          = $mdata['validationCode'];
                $element['script3msg']           = $mdata['validationMessage'];
                $element['functionNameScript3']  = $mdata['validationFunctionName'];
                $element['script3cond']          = $mdata['validationCondition'];
                // Init
                $element['script1id']            = $mdata['initId'];
                $element['script1code']          = $mdata['initCode'];
                $element['script1flag1']         = $mdata['initFormEntry'] ?? '';
                $element['script1flag2']         = $mdata['initPageEntry'] ?? '';
                $element['functionNameScript1']  = $mdata['initFunctionName'];
                $element['script1cond']          = $mdata['initCondition'];
                // Action
                $element['script2id']            = $mdata['actionId'];
                $element['script2code']          = $mdata['actionCode'] ?? '';
                $element['script2flag1']         = $mdata['actionClick'] ?? '';
                $element['script2flag2']         = $mdata['actionBlur'] ?? '';
                $element['script2flag3']         = $mdata['actionChange'] ?? '';
                $element['script2flag4']         = $mdata['actionFocus'] ?? '';
                $element['script2flag5']         = $mdata['actionSelect'] ?? '';
                $element['functionNameScript2']  = $mdata['actionFunctionName'];
                $element['script2cond']          = $mdata['actionCondition'] ?? '';
                $element['hideInMailback']       = $mdata['hideInMailback'] ?? false;
                $element['page']                 = $page;
                $element['dbId']                 = $mdata['dbId'];
                $element['qId']                  = $dataObject['attributes']['id'];

                $areas->container[0]['elements'][] = $element;
            }
        }

        if (!empty($dataObject['children'])) {
            foreach ($dataObject['children'] as $child) {
                $this->createAreasFromTree($child, $areas, $page);
            }
        }
    }

    private function updateDbId(array &$dataObject, mixed $id, int $dbId): void
    {
        if (isset($dataObject['attributes'], $dataObject['properties'])) {
            if ($dataObject['properties']['type'] === 'element' && $dataObject['attributes']['id'] === $id) {
                $dataObject['properties']['dbId'] = $dbId;
                return;
            }
        }

        if (!empty($dataObject['children'])) {
            foreach ($dataObject['children'] as &$child) {
                $this->updateDbId($child, $id, $dbId);
            }
        }
    }

    private function save2(int $form, string $formName, string $formTitle, string $formDesc, string $templateCode, array $areas, int $pages = 1): int
    {
        $dataObject = json_decode(base64_decode($templateCode), true);
        $mdata      = $dataObject['properties'];
        $now        = Factory::getDate()->toSql();
        $userId     = (string) Factory::getApplication()->getIdentity()->username;

        $this->db->setQuery('SELECT id FROM #__facileforms_forms WHERE id = ' . $this->db->quote($form));

        if (count($this->db->loadObjectList()) === 0) {
            // INSERT new form
            $scriptCond1 = '';
            $scriptCond2 = '';
            if (($mdata['submittedScriptCondidtion'] ?? -1) != -1) {
                $scriptCond1 = ', script2cond, script2code';
                $scriptCond2 = ', ' . $this->db->quote($mdata['submittedScriptCondidtion']) .
                               ', ' . $this->db->quote($mdata['submittedScriptCode']);
            }

            $this->db->setQuery(
                "INSERT INTO #__facileforms_forms
                 (package, template_code, template_areas, published, name, title, description,
                  class1, width, height, pages, emailntf, emailadr
                  {$scriptCond1}, created, created_by, modified, modified_by)
                 VALUES
                 ('QuickModeForms',
                  " . trim($this->db->quote($templateCode), "\t, ,\n,\r") . ",
                  " . $this->db->quote((string) json_encode($areas)) . ",
                  '1',
                  " . trim($this->db->quote($formName), "\t, ,\n,\r") . ",
                  " . trim($this->db->quote($formTitle), "\t, ,\n,\r") . ",
                  " . trim($this->db->quote($formDesc), "\t, ,\n,\r") . ",
                  '', '400', '500',
                  " . $this->db->quote($pages) . ",
                  " . $this->db->quote(($mdata['mailNotification'] ?? false) ? 2 : 0) . ",
                  " . $this->db->quote($mdata['mailRecipient'] ?? '') . "
                  {$scriptCond2},
                  " . $this->db->quote($now) . ', ' . $this->db->quote($userId) . ',
                  ' . $this->db->quote($now) . ', ' . $this->db->quote($userId) . ')'
            );
            $this->db->execute();
            $form = (int) $this->db->insertid();
        } else {
            // UPDATE — split template_code into 60 KB chunks to avoid MySQL gone-away errors
            $chunks     = $this->chunkString($templateCode, 60000);
            $scriptCond = '';
            if (($mdata['submittedScriptCondidtion'] ?? -1) != -1) {
                $scriptCond = ', script2cond = ' . $this->db->quote($mdata['submittedScriptCondidtion']) .
                              ', script2code = ' . $this->db->quote($mdata['submittedScriptCode']);
            }

            $emailntf  = 0;
            $recipient = trim((string) ($mdata['mailRecipient'] ?? ''));
            if (!empty($mdata['mailNotification']) && $recipient === '') {
                $emailntf = 1;
            } elseif (!empty($mdata['mailNotification']) && $recipient !== '') {
                $emailntf = 2;
            }

            $this->db->setQuery(
                'UPDATE #__facileforms_forms SET
                 template_code = \'\',
                 template_areas = ' . $this->db->quote((string) json_encode($areas)) . ',
                 name = ' . trim($this->db->quote($formName), "\t, ,\n,\r") . ',
                 title = ' . trim($this->db->quote($formTitle), "\t, ,\n,\r") . ',
                 description = ' . trim($this->db->quote($formDesc), "\t, ,\n,\r") . ',
                 pages = ' . $this->db->quote($pages) . ',
                 emailntf = ' . $this->db->quote($emailntf) . ',
                 emailadr = ' . $this->db->quote($mdata['mailRecipient'] ?? '') . "
                 {$scriptCond},
                 modified = " . $this->db->quote($now) . ',
                 modified_by = ' . $this->db->quote($userId) . '
                 WHERE id = ' . $this->db->quote($form)
            );
            $this->db->execute();

            foreach ($chunks as $chunk) {
                $this->db->setQuery(
                    'UPDATE #__facileforms_forms SET template_code = CONCAT(template_code, ' .
                    $this->db->quote($chunk) . ') WHERE id = ' . $this->db->quote($form)
                );
                $this->db->execute();
            }
        }

        // Sync elements
        $notRemoveIds = '';
        $elementCount = 0;

        foreach ($areas[0]['elements'] as $element) {
            $elementId = -1;

            if ($element['dbId'] == 0) {
                $this->db->setQuery(
                    'INSERT INTO #__facileforms_elements
                     (mailback, mailbackfile, form, page, published, ordering, name, title, type,
                      class1, class2, logging, posx, posxmode, posy, posymode, width, widthmode,
                      height, heightmode, flag1, flag2, data1, data2, data3,
                      script1cond, script1id, script1code, script1flag1, script1flag2,
                      script2cond, script2id, script2code,
                      script2flag1, script2flag2, script2flag3, script2flag4, script2flag5,
                      script3cond, script3id, script3code, script3msg)
                     VALUES (' .
                    $this->db->quote($element['mailback']) . ',' .
                    $this->db->quote($element['mailbackfile']) . ',' .
                    $this->db->quote($form) . ',' .
                    $this->db->quote($element['page'] ?? 1) . ',' .
                    "'1'," .
                    $this->db->quote($element['orderNumber']) . ',' .
                    $this->db->quote($element['name']) . ',' .
                    $this->db->quote($element['title']) . ',' .
                    $this->db->quote($element['bfType']) . ",'','','" .
                    (int) ($element['logging'] ?? 1) . "','0','0','" . (40 * $elementCount) . "','0','20','0','20','0'," .
                    $this->db->quote($element['flag1']) . ',' .
                    $this->db->quote($element['flag2']) . ',' .
                    $this->db->quote($element['data1']) . ',' .
                    $this->db->quote($element['data2']) . ',' .
                    $this->db->quote($element['data3']) . ',' .
                    $this->db->quote($element['script1cond']) . ',' .
                    $this->db->quote($element['script1id']) . ',' .
                    $this->db->quote($element['script1code']) . ',' .
                    $this->db->quote($element['script1flag1']) . ',' .
                    $this->db->quote($element['script1flag2']) . ',' .
                    $this->db->quote($element['script2cond']) . ',' .
                    $this->db->quote($element['script2id']) . ',' .
                    $this->db->quote($element['script2code']) . ',' .
                    $this->db->quote($element['script2flag1']) . ',' .
                    $this->db->quote($element['script2flag2']) . ',' .
                    $this->db->quote($element['script2flag3']) . ',' .
                    $this->db->quote($element['script2flag4']) . ',' .
                    $this->db->quote($element['script2flag5']) . ',' .
                    $this->db->quote($element['script3cond']) . ',' .
                    $this->db->quote($element['script3id']) . ',' .
                    $this->db->quote($element['script3code']) . ',' .
                    $this->db->quote($element['script3msg']) . ')'
                );

                $bError = false;
                try {
                    $this->db->execute();
                } catch (\InvalidArgumentException $e) {
                    $bError = true;
                }

                if (!$bError) {
                    $elementId = (int) $this->db->insertid();
                    $areas[0]['elements'][$elementCount]['dbId'] = $elementId;
                    $this->updateDbId($dataObject, $areas[0]['elements'][$elementCount]['qId'], $elementId);
                }
            } else {
                // Fix ids of copied elements
                $this->db->setQuery(
                    'SELECT id FROM #__facileforms_elements WHERE name = ' . $this->db->quote($element['name']) .
                    ' AND form = ' . $this->db->quote($form)
                );

                $elementCheck = [];
                try {
                    $elementCheck = $this->db->loadObjectList();
                } catch (\InvalidArgumentException $e) {
                    // ignore
                }

                foreach ($elementCheck as $check) {
                    if ((int) $check->id !== (int) $element['dbId']) {
                        $element['dbId'] = (int) $check->id;
                        $areas[0]['elements'][$elementCount]['dbId'] = (int) $check->id;
                        $this->updateDbId($dataObject, $areas[0]['elements'][$elementCount]['qId'], (int) $check->id);
                    }
                }

                $this->db->setQuery(
                    'UPDATE #__facileforms_elements SET' .
                    ' mailback=' . $this->db->quote($element['mailback']) . ',' .
                    ' mailbackfile=' . $this->db->quote($element['mailbackfile']) . ',' .
                    ' form=' . $this->db->quote($form) . ',' .
                    ' page=' . $this->db->quote($element['page'] ?? 1) . ',' .
                    " published='1'," .
                    ' ordering=' . $this->db->quote($element['orderNumber']) . ',' .
                    ' name=' . $this->db->quote($element['name']) . ',' .
                    ' title=' . $this->db->quote($element['title']) . ',' .
                    ' type=' . $this->db->quote($element['bfType']) . "," .
                    " class1='',class2=''," .
                    ' logging=' . $this->db->quote((int) ($element['logging'] ?? 1)) . ',' .
                    " posx='0',posxmode='0'," .
                    " posy='" . (40 * $elementCount) . "',posymode='0'," .
                    " width='20',widthmode='0',height='20',heightmode='0'," .
                    ' flag1=' . $this->db->quote($element['flag1']) . ',' .
                    ' flag2=' . $this->db->quote($element['flag2']) . ',' .
                    ' data1=' . $this->db->quote($element['data1']) . ',' .
                    ' data2=' . $this->db->quote($element['data2']) . ',' .
                    ' data3=' . $this->db->quote($element['data3']) . ',' .
                    ' script1cond=' . $this->db->quote($element['script1cond']) . ',' .
                    ' script1id=' . $this->db->quote($element['script1id']) . ',' .
                    ' script1code=' . $this->db->quote($element['script1code']) . ',' .
                    ' script1flag1=' . $this->db->quote($element['script1flag1']) . ',' .
                    ' script1flag2=' . $this->db->quote($element['script1flag2']) . ',' .
                    ' script2cond=' . $this->db->quote($element['script2cond']) . ',' .
                    ' script2id=' . $this->db->quote($element['script2id']) . ',' .
                    ' script2code=' . $this->db->quote($element['script2code']) . ',' .
                    ' script2flag1=' . $this->db->quote($element['script2flag1']) . ',' .
                    ' script2flag2=' . $this->db->quote($element['script2flag2']) . ',' .
                    ' script2flag3=' . $this->db->quote($element['script2flag3']) . ',' .
                    ' script2flag4=' . $this->db->quote($element['script2flag4']) . ',' .
                    ' script2flag5=' . $this->db->quote($element['script2flag5']) . ',' .
                    ' script3cond=' . $this->db->quote($element['script3cond']) . ',' .
                    ' script3id=' . $this->db->quote($element['script3id']) . ',' .
                    ' script3code=' . $this->db->quote($element['script3code']) . ',' .
                    ' script3msg=' . $this->db->quote($element['script3msg']) .
                    ' WHERE id = ' . $this->db->quote($element['dbId'])
                );

                try {
                    $this->db->execute();
                } catch (\InvalidArgumentException $e) {
                    // ignore
                }

                $elementId = (int) $element['dbId'];
            }

            $notRemoveIds .= ' id <> ' . $this->db->quote($elementId) . ' AND ';
            $elementCount++;
        }

        // Delete elements not in the current save set
        if ($notRemoveIds !== '') {
            $this->db->setQuery('DELETE FROM #__facileforms_elements WHERE ' . $notRemoveIds . 'form = ' . $this->db->quote($form));
        } else {
            $this->db->setQuery('DELETE FROM #__facileforms_elements WHERE form = ' . $this->db->quote($form));
        }
        $this->db->execute();

        // Write final template_code (chunked)
        $finalCode   = base64_encode((string) json_encode($dataObject));
        $finalChunks = $this->chunkString($finalCode, 60000);

        $this->db->setQuery(
            "UPDATE #__facileforms_forms SET template_code = '', template_code_processed = 'QuickMode'," .
            ' template_areas = ' . $this->db->quote((string) json_encode($areas)) .
            ' WHERE id = ' . $this->db->quote($form)
        );
        $this->db->execute();

        foreach ($finalChunks as $chunk) {
            $this->db->setQuery(
                'UPDATE #__facileforms_forms SET template_code = CONCAT(template_code, ' .
                $this->db->quote($chunk) . ') WHERE id = ' . $this->db->quote($form)
            );
            $this->db->execute();
        }

        return $form;
    }

    private function chunkString(string $str, int $size): array
    {
        $chunks = [];
        $length = strlen($str);
        $chunk  = '';
        $cnt    = 0;

        for ($i = 0; $i < $length; $i++) {
            $chunk .= $str[$i];
            $cnt++;
            if ($cnt === $size || $i + 1 === $length) {
                $chunks[] = $chunk;
                $chunk    = '';
                $cnt      = 0;
            }
        }

        return $chunks;
    }
}
