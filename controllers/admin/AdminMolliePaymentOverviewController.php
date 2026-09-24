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

use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Utility\DashboardUrlProvider;
use Mollie\Utility\PaymentOverviewUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Read only view of payment attempts that never became an order: failures, cancellations,
 * expiries, and attempts abandoned at open or pending. Everything on this page comes from
 * mollie_payments; the page never calls the Mollie API.
 */
class AdminMolliePaymentOverviewController extends ModuleAdminController
{
    /** @var Mollie */
    public $module;

    const FILE_NAME = 'AdminMolliePaymentOverviewController';

    /** @var array */
    private $statusLabels;

    /** @var array */
    private $reasonLabels;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mollie_payments';
        $this->identifier = 'transaction_id';
        $this->lang = false;
        $this->noLink = true;
        $this->list_no_link = true;

        parent::__construct();

        $this->actions = [];
        $this->bulk_actions = [];

        $this->statusLabels = $this->getStatusLabels();
        $this->reasonLabels = $this->getReasonLabels();

        $this->fields_list = [
            'created_at' => [
                'title' => $this->module->l('Date', self::FILE_NAME),
                'type' => 'datetime',
                'filter_key' => 'a!created_at',
                'align' => 'text-left',
            ],
            'customer' => [
                'title' => $this->module->l('Customer', self::FILE_NAME),
                'filter_key' => 'cu!lastname',
                // Sorting resolves against the select alias, not cu.lastname. Without an explicit
                // order_key core copies filter_key into it, and a request carrying
                // Orderby=customer then emits ORDER BY cu.`lastname` while the customer join is
                // only added for the filter, which is a hard 500. The alias also sorts by what
                // the column actually shows.
                'order_key' => 'customer',
                'callback' => 'printCustomer',
                'orderby' => false,
            ],
            'method' => [
                'title' => $this->module->l('Method', self::FILE_NAME),
                'filter_key' => 'a!method',
                'callback' => 'printMethod',
            ],
            'bank_status' => [
                'title' => $this->module->l('Status', self::FILE_NAME),
                'type' => 'select',
                'list' => $this->statusLabels,
                'filter_key' => 'a!bank_status',
                'align' => 'text-center',
                'callback' => 'printStatusBadge',
            ],
            'failure_reason' => [
                'title' => $this->module->l('Reason', self::FILE_NAME),
                'callback' => 'printReason',
                'search' => false,
                'orderby' => false,
            ],
            'ps_order_reference' => [
                'title' => $this->module->l('Order', self::FILE_NAME),
                'callback' => 'printOrderLink',
                'align' => 'text-center',
                'search' => false,
                'orderby' => false,
                'remove_onclick' => true,
            ],
            'transaction_id' => [
                'title' => $this->module->l('Transaction', self::FILE_NAME),
                'filter_key' => 'a!transaction_id',
                'callback' => 'printDashboardLink',
                'orderby' => false,
                'remove_onclick' => true,
            ],
        ];

        $this->_orderBy = 'created_at';
        $this->_orderWay = 'desc';
        $this->_use_found_rows = false;

        // Correlated subqueries rather than joins, because core builds its separate COUNT(*)
        // from the same FROM and JOIN clauses. A join would make that count resolve the cart,
        // customer and order of every matching row only to throw the values away; a subquery in
        // the select list is dropped from the count entirely and runs only for the rows the
        // LIMIT actually returns. See getList() for the one case that still needs the join.
        $this->_select .= '
            IFNULL(a.`reason`, "") AS `failure_reason`,
            IFNULL((
                SELECT CONCAT(LEFT(scu.`firstname`, 1), ". ", scu.`lastname`)
                FROM `' . _DB_PREFIX_ . 'cart` sc
                INNER JOIN `' . _DB_PREFIX_ . 'customer` scu ON (scu.`id_customer` = sc.`id_customer`)
                WHERE sc.`id_cart` = a.`cart_id`
            ), "") AS `customer`,
            IFNULL((
                SELECT so.`reference`
                FROM `' . _DB_PREFIX_ . 'orders` so
                WHERE so.`id_order` = a.`order_id` AND a.`order_id` > 0
            ), "") AS `ps_order_reference`,
            a.`order_id` AS `ps_order_id`
        ';

        $this->_where .= ' AND ' . PaymentOverviewUtility::buildOverviewWhereClause(
            'a',
            Config::MOLLIE_PAYMENT_OVERVIEW_STUCK_GRACE_HOURS
        );

        $this->_where .= $this->getShopRestriction();
    }

    /**
     * The customer filter is the only clause that cannot be answered from mollie_payments alone,
     * so the cart and customer tables are joined in for that request and no other. Left in
     * permanently they would also land in core's separate COUNT(*), which is where the cost of a
     * join is paid on a large table; added here they cost nothing on the default view, and when
     * the filter is present it is selective enough that MySQL drives the plan from ps_customer.
     *
     * processFilter() has already run by this point, so _filter is final. It renders the alias
     * unquoted, as `cu`.`lastname` with a bare alias, hence the fragment matched below.
     *
     * AdminControllerCore::$_filter is a SQL fragment at runtime, but its docblock declares it
     * array on 1.7.6 and 1.7.7 and string from 1.7.8 on. The array cast reads correctly under
     * both, since casting a string yields a single element array.
     *
     * @param int $idLang
     * @param string|null $orderBy
     * @param string|null $orderWay
     * @param int $start
     * @param int|null $limit
     * @param int|bool $idLangShop
     */
    public function getList($idLang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $idLangShop = false): void
    {
        if (false !== strpos(implode(' ', (array) $this->_filter), 'cu.`lastname`')) {
            $this->_join .= '
                LEFT JOIN `' . _DB_PREFIX_ . 'cart` c ON (c.`id_cart` = a.`cart_id`)
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` cu ON (cu.`id_customer` = c.`id_customer`)
            ';
        }

        parent::getList($idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);
    }

    public function initContent(): void
    {
        // The page is a read only view, nothing can be created from it. This has to happen here
        // rather than in the constructor: initToolbar() runs in between and re-adds the button.
        if (isset($this->toolbar_btn['new'])) {
            unset($this->toolbar_btn['new']);
        }

        $this->content .= $this->displayIntroduction();

        parent::initContent();
    }

    /**
     * @return false|string
     *
     * @throws SmartyException
     */
    public function displayIntroduction()
    {
        $this->context->smarty->assign([
            'payment_overview_grace_hours' => Config::MOLLIE_PAYMENT_OVERVIEW_STUCK_GRACE_HOURS,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/payment-overview/payment_overview_intro.tpl"
        );
    }

    /**
     * @param string $customer
     *
     * @return string
     */
    public function printCustomer($customer)
    {
        if ('' === (string) $customer) {
            return '<span class="text-muted">' . $this->module->l('Guest or deleted', self::FILE_NAME) . '</span>';
        }

        return htmlspecialchars((string) $customer, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Resolves the display name from the static Config map rather than the payment method
     * repository, so the page stays at a constant query count no matter how many rows it shows.
     *
     * @param string $method
     *
     * @return string
     */
    public function printMethod($method)
    {
        $method = (string) $method;

        if ('' === $method) {
            return '<span class="text-muted">' . $this->module->l('Unknown', self::FILE_NAME) . '</span>';
        }

        $name = isset(Config::$methods[$method]) ? Config::$methods[$method] : $method;

        return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param string $status
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printStatusBadge($status)
    {
        $status = (string) $status;

        $this->context->smarty->assign([
            'payment_status' => $status,
            'payment_status_label' => isset($this->statusLabels[$status]) ? $this->statusLabels[$status] : $status,
            'payment_status_failed' => PaymentStatus::STATUS_FAILED,
            'payment_status_canceled' => PaymentStatus::STATUS_CANCELED,
            'payment_status_expired' => PaymentStatus::STATUS_EXPIRED,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/payment-overview/payment_status_badge.tpl"
        );
    }

    /**
     * Only cards, card wallets and SEPA Direct Debit report a machine readable failure code, and
     * only on an outright failure. Everything else falls back to a label derived from the status,
     * so the column is never empty and never invents a cause.
     *
     * @param string $reason
     * @param array $row
     *
     * @return string
     */
    public function printReason($reason, array $row)
    {
        $reason = (string) $reason;

        if ('' !== $reason && isset($this->reasonLabels[$reason])) {
            return htmlspecialchars($this->reasonLabels[$reason], ENT_QUOTES, 'UTF-8');
        }

        if ('' !== $reason) {
            return htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');
        }

        $status = isset($row['bank_status']) ? (string) $row['bank_status'] : '';

        return '<span class="text-muted">' . htmlspecialchars($this->getFallbackReason($status), ENT_QUOTES, 'UTF-8') . '</span>';
    }

    /**
     * @param string $reference
     * @param array $row
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printOrderLink($reference, array $row)
    {
        $reference = (string) $reference;

        // ps_order_id comes straight off mollie_payments, so it can still point at an order that
        // has since been deleted. The reference is the value that proves the row is really there,
        // because its subquery reads ps_orders; an empty one means there is nothing to link to.
        $orderId = '' === $reference ? 0 : (int) $row['ps_order_id'];

        $this->context->smarty->assign([
            'payment_order_id' => $orderId,
            'payment_order_reference' => $reference,
            'payment_order_url' => $orderId ? $this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $orderId, 'vieworder' => 1]) : '',
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/payment-overview/payment_order_link.tpl"
        );
    }

    /**
     * @param string $transactionId
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printDashboardLink($transactionId)
    {
        $this->context->smarty->assign([
            'payment_transaction_id' => (string) $transactionId,
            'payment_dashboard_url' => (string) DashboardUrlProvider::getTransactionDashboardUrl($transactionId),
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/payment-overview/payment_dashboard_link.tpl"
        );
    }

    /**
     * mollie_payments has no id_shop of its own, so the list is scoped through the cart it was
     * created from. An attempt whose cart row was pruned has no shop to attribute it to and is
     * therefore only visible in the all shops context.
     *
     * @return string
     */
    private function getShopRestriction()
    {
        if (!Shop::isFeatureActive()) {
            return '';
        }

        if (Shop::CONTEXT_ALL === Shop::getContext()) {
            return '';
        }

        $shopIds = array_map('intval', Shop::getContextListShopID());

        if (empty($shopIds)) {
            return '';
        }

        // A subquery rather than a join on the cart table, for the same reason as the select
        // list: core reuses the join clause in its separate COUNT(*). An attempt whose cart row
        // has been pruned has no shop to attribute it to and drops out of a shop scoped view,
        // which is the safer of the two failure modes; it stays visible in All shops context.
        return ' AND a.`cart_id` IN (
            SELECT shc.`id_cart` FROM `' . _DB_PREFIX_ . 'cart` shc
            WHERE shc.`id_shop` IN (' . implode(', ', $shopIds) . ')
        )';
    }

    /**
     * @return array
     */
    private function getStatusLabels()
    {
        return [
            PaymentStatus::STATUS_FAILED => $this->module->l('Failed', self::FILE_NAME),
            PaymentStatus::STATUS_CANCELED => $this->module->l('Canceled', self::FILE_NAME),
            PaymentStatus::STATUS_EXPIRED => $this->module->l('Expired', self::FILE_NAME),
            PaymentStatus::STATUS_OPEN => $this->module->l('Abandoned', self::FILE_NAME),
            PaymentStatus::STATUS_PENDING => $this->module->l('Abandoned', self::FILE_NAME),
        ];
    }

    /**
     * @param string $status
     *
     * @return string
     */
    private function getFallbackReason($status)
    {
        if (PaymentStatus::STATUS_EXPIRED === $status) {
            return $this->module->l('Not completed in time', self::FILE_NAME);
        }

        if (PaymentStatus::STATUS_CANCELED === $status) {
            return $this->module->l('Canceled by the customer', self::FILE_NAME);
        }

        if (in_array($status, PaymentOverviewUtility::STUCK_STATUSES, true)) {
            return $this->module->l('Checkout never finished', self::FILE_NAME);
        }

        return $this->module->l('No reason reported', self::FILE_NAME);
    }

    /**
     * @return array
     */
    private function getReasonLabels()
    {
        return [
            'authentication_abandoned' => $this->module->l('3-D Secure abandoned', self::FILE_NAME),
            'authentication_failed' => $this->module->l('3-D Secure failed', self::FILE_NAME),
            'authentication_required' => $this->module->l('3-D Secure required', self::FILE_NAME),
            'authentication_unavailable_acs' => $this->module->l('3-D Secure unavailable', self::FILE_NAME),
            'card_declined' => $this->module->l('Card declined', self::FILE_NAME),
            'card_expired' => $this->module->l('Card expired', self::FILE_NAME),
            'inactive_card' => $this->module->l('Card not active', self::FILE_NAME),
            'insufficient_funds' => $this->module->l('Insufficient funds', self::FILE_NAME),
            'invalid_cvv' => $this->module->l('Invalid security code', self::FILE_NAME),
            'invalid_card_holder_name' => $this->module->l('Invalid card holder name', self::FILE_NAME),
            'invalid_card_number' => $this->module->l('Invalid card number', self::FILE_NAME),
            'invalid_card_type' => $this->module->l('Card type not accepted', self::FILE_NAME),
            'possible_fraud' => $this->module->l('Suspected fraud', self::FILE_NAME),
            'refused_by_issuer' => $this->module->l('Refused by the bank', self::FILE_NAME),
            'unknown_reason' => $this->module->l('Refused, no reason given', self::FILE_NAME),
            Config::WRONG_AMOUNT_REASON => $this->module->l('Amount did not match the cart', self::FILE_NAME),
        ];
    }
}
