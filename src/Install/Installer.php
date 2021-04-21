<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Install;

use Configuration;
use Db;
use DbQuery;
use Exception;
use Feature;
use FeatureValue;
use Language;
use Mollie;
use Mollie\Config\Config;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Service\OrderStateImageService;
use Mollie\Tracker\Segment;
use Mollie\Utility\MultiLangUtility;
use OrderState;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tab;
use Tools;
use Validate;

class Installer implements InstallerInterface
{
	const FILE_NAME = 'Installer';

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var OrderStateImageService
	 */
	private $imageService;

	/**
	 * @var InstallerInterface
	 */
	private $databaseTableInstaller;

	/**
	 * @var Segment
	 */
	private $segment;

	public function __construct(
		Mollie $module,
		OrderStateImageService $imageService,
		InstallerInterface $databaseTableInstaller,
		Segment $segment
	) {
		$this->module = $module;
		$this->imageService = $imageService;
		$this->databaseTableInstaller = $databaseTableInstaller;
		$this->segment = $segment;
	}

	public function install()
	{
		$this->segment->setMessage('Mollie installed');
		$this->segment->track();
		$errorHandler = ErrorHandler::getInstance();

		foreach (self::getHooks() as $hook) {
			if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && 'displayPaymentEU' === $hook) {
				continue;
			}

			$this->module->registerHook($hook);
		}

		try {
			$this->createMollieStatuses();
		} catch (Exception $e) {
			$errorHandler->handle($e, $e->getCode(), false);
			$this->errors[] = $this->module->l('Unable to install Mollie statuses', self::FILE_NAME);

			return false;
		}

		try {
			$this->initConfig();
		} catch (Exception $e) {
			$errorHandler->handle($e, $e->getCode(), false);
			$this->errors[] = $this->module->l('Unable to install config', self::FILE_NAME);

			return false;
		}
		try {
			$this->setDefaultCarrierStatuses();
		} catch (Exception $e) {
			$errorHandler->handle($e, $e->getCode(), false);
			$this->errors[] = $this->module->l('Unable to install default carrier statuses', self::FILE_NAME);

			return false;
		}

		try {
			$this->installTab('AdminMollieAjax', 0, 'AdminMollieAjax', false);
			$this->installTab('AdminMollieModule', 'IMPROVE', 'Mollie', true, 'mollie');
		} catch (Exception $e) {
			$errorHandler->handle($e, $e->getCode(), false);
			$this->errors[] = $this->module->l('Unable to install new controllers', self::FILE_NAME);

			return false;
		}

		try {
			$this->installVoucherFeatures();
		} catch (Exception $e) {
			$errorHandler->handle($e, $e->getCode(), false);
			$this->errors[] = $this->module->l('Unable to install voucher attributes', self::FILE_NAME);

			return false;
		}

		$this->copyEmailTemplates();

		return $this->databaseTableInstaller->install();
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public static function getHooks()
	{
		return [
			'displayPayment',
			'displayPaymentEU',
			'paymentOptions',
			'displayAdminOrder',
			'displayBackOfficeHeader',
			'displayOrderConfirmation',
			'actionFrontControllerSetMedia',
			'actionEmailSendBefore',
			'actionOrderStatusUpdate',
			'displayPDFInvoice',
			'actionAdminOrdersListingFieldsModifier',
			'actionAdminStatusesListingFieldsModifier',
			'actionAdminControllerSetMedia',
			'actionValidateOrder',
			'actionOrderGridDefinitionModifier',
			'actionOrderGridQueryBuilderModifier',
			'actionObjectCurrencyUpdateAfter',
		];
	}

	/**
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	private function createPartialRefundOrderState()
	{
		$orderState = new OrderState();
		$orderState->send_email = false;
		$orderState->color = '#6F8C9F';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = false;
		$orderState->invoice = false;
		$orderState->module_name = $this->module->name;
		$orderState->name = MultiLangUtility::createMultiLangField('Mollie partially refunded');
		if ($orderState->add()) {
			$this->imageService->createOrderStateLogo($orderState->id);
		}
		Configuration::updateValue(Config::MOLLIE_STATUS_PARTIAL_REFUND, (int) $orderState->id);

		return true;
	}

	/**
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function createPartialShippedOrderState()
	{
		$orderState = new OrderState();
		$orderState->send_email = false;
		$orderState->color = '#8A2BE2';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = false;
		$orderState->invoice = false;
		$orderState->module_name = $this->module->name;
		$orderState->name = MultiLangUtility::createMultiLangField('Partially shipped');

		if ($orderState->add()) {
			$this->imageService->createOrderStateLogo($orderState->id);
		}
		Configuration::updateValue(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED, (int) $orderState->id);

		return true;
	}

	public function createMollieStatuses()
	{
		if (!$this->createPartialRefundOrderState()) {
			return false;
		}
		if (!$this->createAwaitingMollieOrderState()) {
			return false;
		}
		if (!$this->createPartialShippedOrderState()) {
			return false;
		}
		if (!$this->createOrderCompletedOrderState()) {
			return false;
		}
		if (!$this->klarnaPaymentAuthorizedState()) {
			return false;
		}
		if (!$this->klarnaPaymentShippedState()) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function createAwaitingMollieOrderState()
	{
		$orderState = new OrderState();
		$orderState->send_email = false;
		$orderState->color = '#4169E1';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = false;
		$orderState->invoice = false;
		$orderState->module_name = $this->module->name;
		$orderState->name = MultiLangUtility::createMultiLangField('Awaiting Mollie payment');

		if ($orderState->add()) {
			$this->imageService->createOrderStateLogo($orderState->id);
		}
		Configuration::updateValue(Config::MOLLIE_STATUS_AWAITING, (int) $orderState->id);

		return true;
	}

	/**
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function createOrderCompletedOrderState()
	{
		$orderState = new OrderState();
		$orderState->send_email = false;
		$orderState->color = '#3d7d1c';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = false;
		$orderState->invoice = false;
		$orderState->send_email = true;
		$orderState->module_name = $this->module->name;
		$orderState->name = MultiLangUtility::createMultiLangField('Completed');

		if ($orderState->add()) {
			$this->imageService->createOrderStateLogo($orderState->id);
		}
		Configuration::updateValue(Config::MOLLIE_STATUS_ORDER_COMPLETED, (int) $orderState->id);

		return true;
	}

	/**
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function klarnaPaymentAuthorizedState()
	{
		$orderState = new OrderState();
		$orderState->send_email = true;
		$orderState->color = '#8A2BE2';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = true;
		$orderState->invoice = true;
		$orderState->pdf_invoice = true;
		$orderState->paid = true;
		$orderState->send_email = true;
		$orderState->template = 'payment';
		$orderState->module_name = $this->module->name;
		$orderState->name = MultiLangUtility::createMultiLangField('Klarna payment authorized');

		if ($orderState->add()) {
			$this->imageService->createOrderStateLogo($orderState->id);
		}
		Configuration::updateValue(Config::MOLLIE_STATUS_KLARNA_AUTHORIZED, (int) $orderState->id);
		Configuration::updateValue(Config::MOLLIE_KLARNA_INVOICE_ON, Config::MOLLIE_STATUS_KLARNA_AUTHORIZED);

		return true;
	}

	/**
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function klarnaPaymentShippedState()
	{
		$orderState = new OrderState();
		$orderState->send_email = true;
		$orderState->color = '#8A2BE2';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = true;
		$orderState->invoice = false;
		$orderState->shipped = true;
		$orderState->paid = true;
		$orderState->delivery = true;
		$orderState->template = 'shipped';
		$orderState->pdf_invoice = true;
		$orderState->module_name = $this->module->name;
		$orderState->name = MultiLangUtility::createMultiLangField('Klarna payment shipped');

		if ($orderState->add()) {
			$this->imageService->createOrderStateLogo($orderState->id);
		}
		Configuration::updateValue(Config::MOLLIE_STATUS_KLARNA_SHIPPED, (int) $orderState->id);

		return true;
	}

	/**
	 * @return void
	 */
	protected function initConfig()
	{
		Configuration::updateValue(Config::MOLLIE_API_KEY, '');
		Configuration::updateValue(Config::MOLLIE_API_KEY_TEST, '');
		Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, Config::ENVIRONMENT_TEST);
		Configuration::updateValue(Config::MOLLIE_PROFILE_ID, '');
		Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, 0);
		Configuration::updateValue(Config::MOLLIE_SEND_NEW_ORDER, 0);
		Configuration::updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE);
		Configuration::updateValue(Config::MOLLIE_IFRAME, false);
		Configuration::updateValue(Config::MOLLIE_IMAGES, Config::LOGOS_NORMAL);
		Configuration::updateValue(Config::MOLLIE_ISSUERS, Config::ISSUERS_ON_CLICK);
		Configuration::updateValue(Config::MOLLIE_CSS, '');
		Configuration::updateValue(Config::MOLLIE_TRACKING_URLS, '');
		Configuration::updateValue(Config::MOLLIE_DEBUG_LOG, Config::DEBUG_LOG_ERRORS);
		Configuration::updateValue(Config::MOLLIE_METHOD_COUNTRIES, 0);
		Configuration::updateValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, 0);
		Configuration::updateValue(Config::MOLLIE_DISPLAY_ERRORS, false);
		Configuration::updateValue(Config::MOLLIE_STATUS_OPEN, Configuration::get(Config::STATUS_MOLLIE_AWAITING));
		Configuration::updateValue(Config::MOLLIE_STATUS_PAID, Configuration::get('PS_OS_PAYMENT'));
		Configuration::updateValue(Config::MOLLIE_STATUS_COMPLETED, Configuration::get(Config::MOLLIE_STATUS_ORDER_COMPLETED));
		Configuration::updateValue(Config::MOLLIE_STATUS_CANCELED, Configuration::get('PS_OS_CANCELED'));
		Configuration::updateValue(Config::MOLLIE_STATUS_EXPIRED, Configuration::get('PS_OS_CANCELED'));
		Configuration::updateValue(
			Config::MOLLIE_STATUS_PARTIAL_REFUND,
			Configuration::get(Config::MOLLIE_STATUS_PARTIAL_REFUND)
		);
		Configuration::updateValue(Config::MOLLIE_STATUS_REFUNDED, Configuration::get('PS_OS_REFUND'));
		Configuration::updateValue(Config::MOLLIE_STATUS_SHIPPING, Configuration::get(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED));
		Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_SHIPPING, true);
		Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_PAID, true);
		Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_COMPLETED, true);
		Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_CANCELED, true);
		Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_EXPIRED, true);
		Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_REFUNDED, true);
		Configuration::updateValue(Config::MOLLIE_ACCOUNT_SWITCH, false);
		Configuration::updateValue(Config::MOLLIE_CSS, '');

		Configuration::updateValue(Config::MOLLIE_API, Config::MOLLIE_ORDERS_API);
	}

	public function setDefaultCarrierStatuses()
	{
		$sql = new DbQuery();
		$sql->select('`' . bqSQL(OrderState::$definition['primary']) . '`');
		$sql->from(bqSQL(OrderState::$definition['table']));
		$sql->where('`shipped` = 1');

		$defaultStatuses = Db::getInstance()->executeS($sql);
		if (!is_array($defaultStatuses)) {
			return;
		}
		$defaultStatuses = array_map('intval', array_column($defaultStatuses, OrderState::$definition['primary']));
		Configuration::updateValue(Config::MOLLIE_AUTO_SHIP_STATUSES, json_encode($defaultStatuses));
	}

	public function installTab($className, $parent, $name, $active = true, $icon = '')
	{
		$idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

		$moduleTab = new Tab();
		$moduleTab->class_name = $className;
		$moduleTab->id_parent = $idParent;
		$moduleTab->module = $this->module->name;
		$moduleTab->active = $active;
		$moduleTab->icon = $icon; /** @phpstan-ignore-line */
		$languages = Language::getLanguages(true);
		foreach ($languages as $language) {
			$moduleTab->name[$language['id_lang']] = $name;
		}

		if (!$moduleTab->save()) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function copyEmailTemplates()
	{
		$languages = Language::getLanguages(false);

		foreach ($languages as $language) {
			if (Config::DEFAULT_EMAIL_LANGUAGE_ISO_CODE === $language['iso_code']) {
				continue;
			}

			if (file_exists($this->module->getLocalPath() . 'mails/' . $language['iso_code'])) {
				continue;
			}

			try {
				Tools::recurseCopy(
					$this->module->getLocalPath() . 'mails/' . Config::DEFAULT_EMAIL_LANGUAGE_ISO_CODE,
					$this->module->getLocalPath() . 'mails/' . $language['iso_code']
				);
			} catch (PrestaShopException $e) {
				$this->errors[] = $this->module->l('Could not copy email templates:', self::FILE_NAME) . ' ' . $e->getMessage();

				return false;
			}
		}

		return true;
	}

	public function installVoucherFeatures()
	{
		$mollieVoucherId = Configuration::get(Config::MOLLIE_VOUCHER_FEATURE_ID);
		if ($mollieVoucherId) {
			$mollieFeature = new Feature((int) $mollieVoucherId);
			$doesFeatureExist = Validate::isLoadedObject($mollieFeature);
			if ($doesFeatureExist) {
				return;
			}
		}

		$feature = new Feature();
		$feature->name = MultiLangUtility::createMultiLangField('Voucher');
		$feature->add();

		foreach (Config::MOLLIE_VOUCHER_CATEGORIES as $key => $categoryName) {
			$featureValue = new FeatureValue();
			$featureValue->id_feature = $feature->id;
			$featureValue->value = MultiLangUtility::createMultiLangField($categoryName);
			$featureValue->add();
			Configuration::updateValue(Config::MOLLIE_VOUCHER_FEATURE . $key, $featureValue->id);
		}

		Configuration::updateValue(Config::MOLLIE_VOUCHER_FEATURE_ID, $feature->id);
	}
}
