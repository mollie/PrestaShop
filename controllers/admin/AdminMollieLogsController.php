<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

use Mollie\Adapter\Shop;
use Mollie\Config\Config;
use Mollie\Logger\LogFormatter;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\MolLogRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\VersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminMollieLogsController extends ModuleAdminController
{
    /** @var Mollie */
    public $module;

    const FILE_NAME = 'AdminMollieLogsController';

    const LOG_INFORMATION_TYPE_REQUEST = 'request';
    const LOG_INFORMATION_TYPE_RESPONSE = 'response';
    const LOG_INFORMATION_TYPE_CONTEXT = 'context';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'log';
        $this->className = 'PrestaShopLogger';
        $this->lang = false;
        $this->noLink = true;

        parent::__construct();

        $this->toolbar_btn = [];
        $this->fields_list = [
            'id_log' => [
                'title' => $this->module->l('ID', self::FILE_NAME),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'severity' => [
                'title' => $this->module->l('Severity (1-4)', self::FILE_NAME),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'printSeverityLevel',
            ],
            'message' => [
                'title' => $this->module->l('Message', self::FILE_NAME),
            ],
            'request' => [
                'title' => $this->module->l('Request', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printRequestButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'response' => [
                'title' => $this->module->l('Response', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printResponseButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'context' => [
                'title' => $this->module->l('Context', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printContextButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'date_add' => [
                'title' => $this->module->l('Date', self::FILE_NAME),
                'align' => 'right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];

        $this->_orderBy = 'id_log';
        $this->_orderWay = 'desc';

        $this->_select .= '
            REPLACE(a.`message`, "' . LogFormatter::MOLLIE_LOG_PREFIX . '", "") as message,
            kpl.request, kpl.response, kpl.context
        ';

        $shopIdCheck = '';

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            $shopIdCheck = ' AND kpl.id_shop = a.id_shop';
        }

        $this->_join .= ' JOIN ' . _DB_PREFIX_ . 'mol_logs kpl ON (kpl.id_log = a.id_log' . $shopIdCheck . ' AND a.object_type = "' . pSQL(Logger::LOG_OBJECT_TYPE) . '")';
        $this->_use_found_rows = false;
        $this->list_no_link = true;
    }

    /**
     * @return false|string
     *
     * @throws SmartyException
     */
    public function displaySeverityInformation()
    {
        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_levels.tpl"
        );
    }

    /**
     * @throws SmartyException
     */
    public function initContent(): void
    {
        // NOTE: we cannot add new logs here.
        if (isset($this->toolbar_btn['new'])) {
            unset($this->toolbar_btn['new']);
        }

        $this->content .= $this->displaySeverityInformation();

        parent::initContent();
    }

    public function setMedia($isNewTheme = false): void
    {
        parent::setMedia($isNewTheme);

        Media::addJsDef([
            'mollie' => [
                'logsUrl' => Context::getContext()->link->getAdminLink(Mollie::ADMIN_MOLLIE_LOGS_CONTROLLER),
            ],
        ]);

        $this->addJS($this->module->getPathUri() . 'views/js/admin/logs/log.js', false);
        $this->addCss($this->module->getPathUri() . 'views/css/admin/logs/log.css');
    }

    /**
     * @param string $request
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printRequestButton(string $request, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $request, self::LOG_INFORMATION_TYPE_REQUEST);
    }

    /**
     * @param string $response
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printResponseButton(string $response, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $response, self::LOG_INFORMATION_TYPE_RESPONSE);
    }

    /**
     * @param string $context
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printContextButton(string $context, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $context, self::LOG_INFORMATION_TYPE_CONTEXT);
    }

    /**
     * @param int $level
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printSeverityLevel(int $level)
    {
        $this->context->smarty->assign([
            'log_severity_level' => $level,
            'log_severity_level_informative' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE :
                Config::LOG_SEVERITY_LEVEL_INFORMATIVE,
            'log_severity_level_warning' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING :
                Config::LOG_SEVERITY_LEVEL_WARNING,
            'log_severity_level_error' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR :
                Config::LOG_SEVERITY_LEVEL_ERROR,
            'log_severity_level_major' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR :
                Config::LOG_SEVERITY_LEVEL_MAJOR,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_level_column.tpl"
        );
    }

    /**
     * @param int $logId
     * @param string $data
     * @param string $logInformationType
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function getDisplayButton(int $logId, string $data, string $logInformationType)
    {
        $unserializedData = json_decode($data);

        if (empty($unserializedData)) {
            return '--';
        }

        $this->context->smarty->assign([
            'log_id' => $logId,
            'log_information_type' => $logInformationType,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/log_modal.tpl"
        );
    }

    public function displayAjaxGetLog()
    {
        /** @var \Mollie\Adapter\ToolsAdapter $tools */
        $tools = $this->module->getService(\Mollie\Adapter\ToolsAdapter::class);

        /** @var MolLogRepositoryInterface $logRepository */
        $logRepository = $this->module->getService(MolLogRepositoryInterface::class);

        /** @var Shop $shopContext */
        $shopContext = $this->module->getService(Shop::class);

        $logId = $tools->getValueAsInt('log_id');

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            /** @var \MolLog|null $log */
            $log = $logRepository->findOneBy([
                'id_log' => $logId,
                'id_shop' => $shopContext->getShop()->id,
            ]);
        } catch (\Exception $exception) {
            $logger->error('Failed to find log', [
                'context' => [
                    'id_log' => $logId,
                    'id_shop' => $shopContext->getShop()->id,
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $this->ajaxResponse(json_encode([
                'error' => true,
                'message' => $this->module->l('Failed to find log.', self::FILE_NAME),
            ]));
        }

        if (!$log) {
            $logger->error('No log information found.', [
                'context' => [
                    'id_log' => $logId,
                    'id_shop' => $shopContext->getShop()->id,
                ],
                'exceptions' => [],
            ]);

            $this->ajaxResponse(json_encode([
                'error' => true,
                'message' => $this->module->l('No log information found.', self::FILE_NAME),
            ]));
        }

        $this->ajaxResponse(json_encode([
            'error' => false,
            'log' => [
                self::LOG_INFORMATION_TYPE_REQUEST => $log->request,
                self::LOG_INFORMATION_TYPE_RESPONSE => $log->response,
                self::LOG_INFORMATION_TYPE_CONTEXT => $log->context,
            ],
        ]));
    }

    /**
     * @param string|false|null $value
     * @param null $controller
     * @param null $method
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    protected function ajaxResponse($value = null, $controller = null, $method = null): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $this->ajaxDie($value, $controller, $method);
        } catch (\Exception $exception) {
            $logger->error('Could not return ajax response', [
                'context' => [
                    'response' => json_encode($value ?: []),
                    'exceptions' => ExceptionUtility::getExceptions($exception),
                ],
            ]);
        }

        exit;
    }
}
