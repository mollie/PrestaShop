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
use Mollie\Config\Config;
use Mollie\Repository\OrderStateRepository;
use Mollie\Tracker\Segment;
use Tab;

class Uninstall implements UninstallerInterface
{
	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var UninstallerInterface
	 */
	private $databaseUninstaller;

	/**
	 * @var OrderStateRepository
	 */
	private $orderStateRepository;

	/**
	 * @var Segment
	 */
	private $segment;

	public function __construct(
		UninstallerInterface $databaseUninstaller,
		OrderStateRepository $orderStateRepository,
		Segment $segment
	) {
		$this->databaseUninstaller = $databaseUninstaller;
		$this->orderStateRepository = $orderStateRepository;
		$this->segment = $segment;
	}

	public function uninstall()
	{
		$this->segment->setMessage('Mollie uninstall');
		$this->segment->track();

		$this->orderStateRepository->deleteStatuses();

		$this->deleteConfig();

		$this->uninstallTabs();

		$this->databaseUninstaller->uninstall();

		return true;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	private function deleteConfig()
	{
		$configurations = [
			Config::MOLLIE_API_KEY,
			Config::MOLLIE_PROFILE_ID,
			Config::MOLLIE_PAYMENTSCREEN_LOCALE,
			Config::MOLLIE_SEND_ORDER_CONFIRMATION,
			Config::MOLLIE_SEND_NEW_ORDER,
			Config::MOLLIE_IFRAME,
			Config::MOLLIE_IMAGES,
			Config::MOLLIE_ISSUERS,
			Config::MOLLIE_CSS,
			Config::MOLLIE_DEBUG_LOG,
			Config::MOLLIE_DISPLAY_ERRORS,
			Config::MOLLIE_STATUS_OPEN,
			Config::MOLLIE_STATUS_PAID,
			Config::MOLLIE_STATUS_CANCELED,
			Config::MOLLIE_STATUS_EXPIRED,
			Config::MOLLIE_STATUS_PARTIAL_REFUND,
			Config::MOLLIE_STATUS_REFUNDED,
			Config::MOLLIE_STATUS_SHIPPING,
			Config::MOLLIE_MAIL_WHEN_SHIPPING,
			Config::MOLLIE_MAIL_WHEN_OPEN,
			Config::MOLLIE_MAIL_WHEN_PAID,
			Config::MOLLIE_MAIL_WHEN_CANCELED,
			Config::MOLLIE_MAIL_WHEN_EXPIRED,
			Config::MOLLIE_MAIL_WHEN_REFUNDED,
			Config::MOLLIE_ACCOUNT_SWITCH,
			Config::MOLLIE_METHOD_COUNTRIES,
			Config::MOLLIE_METHOD_COUNTRIES_DISPLAY,
			Config::MOLLIE_API,
			Config::MOLLIE_AUTO_SHIP_STATUSES,
			Config::MOLLIE_TRACKING_URLS,
			Config::MOLLIE_METHODS_LAST_CHECK,
			Config::METHODS_CONFIG,
			Config::MOLLIE_STATUS_PARTIALLY_SHIPPED,
			Config::MOLLIE_STATUS_COMPLETED,
			Config::MOLLIE_STATUS_ORDER_COMPLETED,
			Config::MOLLIE_MAIL_WHEN_COMPLETED,
			Config::MOLLIE_STATUS_AWAITING,
			Config::MOLLIE_API_KEY_TEST,
		];

		$this->deleteConfigurations($configurations);
	}

	private function deleteConfigurations(array $configurations)
	{
		foreach ($configurations as $configuration) {
			Configuration::deleteByName($configuration);
		}
	}

	private function uninstallTabs()
	{
		$tabs = [
			'AdminMollieAjax',
			'AdminMollieModule',
		];

		foreach ($tabs as $tab) {
			$idTab = Tab::getIdFromClassName($tab);

			if (!$idTab) {
				continue;
			}

			$tab = new Tab($idTab);
			if (!$tab->delete()) {
				return false;
			}
		}
	}
}
