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

namespace Mollie\Install;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Tracker\Segment;
use Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Uninstall
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
     * @var Segment
     */
    private $segment;
    private $configurationAdapter;

    public function __construct(
        UninstallerInterface $databaseUninstaller,
        Segment $segment,
        ConfigurationAdapter $configurationAdapter
    ) {
        $this->databaseUninstaller = $databaseUninstaller;
        $this->segment = $segment;
        $this->configurationAdapter = $configurationAdapter;
    }

    public function uninstall()
    {
        $this->segment->setMessage('Mollie uninstall');
        $this->segment->track();

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
            Config::MOLLIE_PAYMENTSCREEN_LOCALE,
            Config::MOLLIE_SEND_ORDER_CONFIRMATION,
            Config::MOLLIE_IFRAME,
            Config::MOLLIE_IMAGES,
            Config::MOLLIE_ISSUERS,
            Config::MOLLIE_CSS,
            Config::MOLLIE_DEBUG_LOG,
            Config::MOLLIE_DISPLAY_ERRORS,
            Config::MOLLIE_MAIL_WHEN_SHIPPING,
            Config::MOLLIE_MAIL_WHEN_OPEN,
            Config::MOLLIE_MAIL_WHEN_PAID,
            Config::MOLLIE_MAIL_WHEN_CANCELED,
            Config::MOLLIE_MAIL_WHEN_EXPIRED,
            Config::MOLLIE_MAIL_WHEN_REFUNDED,
            Config::MOLLIE_MAIL_WHEN_CHARGEBACK,
            Config::MOLLIE_ACCOUNT_SWITCH,
            Config::MOLLIE_METHOD_COUNTRIES,
            Config::MOLLIE_METHOD_COUNTRIES_DISPLAY,
            Config::MOLLIE_API,
            Config::MOLLIE_AUTO_SHIP_STATUSES,
            Config::MOLLIE_TRACKING_URLS,
            Config::MOLLIE_METHODS_LAST_CHECK,
            Config::METHODS_CONFIG,
            Config::MOLLIE_MAIL_WHEN_COMPLETED,
            Config::MOLLIE_API_KEY_TEST,
            Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID,
        ];

        $this->deleteConfigurations($configurations);
    }

    private function deleteConfigurations(array $configurations)
    {
        foreach ($configurations as $configuration) {
            $this->configurationAdapter->delete($configuration);
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
