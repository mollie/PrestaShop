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

namespace Mollie\Service\ExpressComponent;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * PrestaShop picks carriers by the groups of the customer on the cart, and a cart without one is
 * judged as a Visitor. The order of an anonymous Express cart goes to a new guest, so its carriers
 * are quoted for a hidden guest of the shop whose only group is the Guest group, the group core
 * checks in a regular guest checkout.
 */
class ExpressQuoteCustomerService
{
    const EMAIL = 'express-shipping-quote@mollie.invalid';
    const NAME = 'Express';

    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var Context */
    private $context;

    public function __construct(ConfigurationAdapter $configuration, Context $context)
    {
        $this->configuration = $configuration;
        $this->context = $context;
    }

    public function getCustomerId(): int
    {
        $shopId = $this->context->getShopId();
        $guestGroupId = (int) $this->configuration->get('PS_GUEST_GROUP');
        $customer = new \Customer((int) $this->configuration->get(Config::MOLLIE_EXPRESS_QUOTE_CUSTOMER_ID, $shopId));

        if (!\Validate::isLoadedObject($customer) || (int) $customer->id_shop !== $shopId) {
            $customer = $this->createCustomer($shopId, $guestGroupId);
            $this->configuration->updateValue(Config::MOLLIE_EXPRESS_QUOTE_CUSTOMER_ID, (int) $customer->id, $shopId);
        }

        // read without Customer::getGroupsStatic(), whose request cache would keep a stale list
        if ($this->getGroupIds((int) $customer->id) !== [$guestGroupId]) {
            $customer->id_default_group = $guestGroupId;
            $customer->update();
            $customer->updateGroup([$guestGroupId]);
        }

        return (int) $customer->id;
    }

    private function createCustomer(int $shopId, int $guestGroupId): \Customer
    {
        $customer = new \Customer();
        $customer->is_guest = true;
        $customer->active = false;
        $customer->deleted = true;
        $customer->firstname = self::NAME;
        $customer->lastname = self::NAME;
        $customer->email = self::EMAIL;
        $customer->passwd = (new Hashing())->hash(bin2hex(random_bytes(16)), _COOKIE_KEY_);
        $customer->id_shop = $shopId;
        $customer->id_default_group = $guestGroupId;
        $customer->groupBox = [$guestGroupId];
        $customer->add();

        return $customer;
    }

    /**
     * @return int[]
     */
    protected function getGroupIds(int $customerId): array
    {
        $rows = \Db::getInstance()->executeS('SELECT id_group FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer = ' . $customerId . ' ORDER BY id_group');

        return array_map('intval', array_column($rows ?: [], 'id_group'));
    }
}
