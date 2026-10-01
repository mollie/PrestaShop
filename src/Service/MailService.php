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

namespace Mollie\Service;

use Address;
use AddressFormat;
use Carrier;
use Configuration;
use Context;
use Customer;
use Hook;
use Language;
use Mail;
use Mollie;
use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\LoggerInterface;
use Mollie\Subscription\Provider\GeneralSubscriptionMailDataProvider;
use Order;
use OrderState;
use PDF;
use Product;
use State;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MailService
{
    const FILE_NAME = 'MailService';

    /** @var Mollie */
    private $module;
    /** @var Context */
    private $context;
    /** @var ProductAttributeAdapter */
    private $productAttributeAdapter;
    /** @var GeneralSubscriptionMailDataProvider */
    private $generalSubscriptionMailDataProvider;
    /** @var ToolsAdapter */
    private $tools;

    public function __construct(
        ModuleFactory $module,
        ProductAttributeAdapter $productAttributeAdapter,
        GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider,
        ToolsAdapter $tools
    ) {
        $this->module = $module->getModule();
        $this->context = Context::getContext();
        $this->productAttributeAdapter = $productAttributeAdapter;
        $this->generalSubscriptionMailDataProvider = $generalSubscriptionMailDataProvider;
        $this->tools = $tools;
    }

    public function sendSecondChanceMail(Customer $customer, $checkoutUrl, $methodName, $shopId)
    {
        $customerLanguage = new Language((int) $customer->id_lang);

        Mail::send(
            $customer->id_lang,
            'mollie_payment',
            $this->module->l('Order payment', self::FILE_NAME, $customerLanguage->locale),
            [
                '{checkoutUrl}' => $checkoutUrl,
                '{firstName}' => $customer->firstname,
                '{lastName}' => $customer->lastname,
                '{paymentMethod}' => $methodName,
            ],
            $customer->email,
            null,
            null,
            null,
            null,
            null,
            $this->module->getLocalPath() . 'mails/',
            false,
            $shopId
        );
    }

    /**
     * @param int $orderStateId
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function sendOrderConfMail(Order $order, $orderStateId)
    {
        $data = $this->getOrderConfData($order);
        $fileAttachment = $this->getFileAttachment($orderStateId, $order);
        $customer = $order->getCustomer();
        $orderLanguage = new Language((int) $order->id_lang);

        Mail::Send(
            (int) $order->id_lang,
            'order_conf',
            $this->context->getTranslatorFromLocale($orderLanguage->locale)->trans('Order confirmation', [], 'Emails.Subject'),
            $data,
            $customer->email,

            implode(' ', [$customer->firstname, $customer->lastname]),
            null,
            null,
            $fileAttachment,
            null, _PS_MAIL_DIR_, false, (int) $order->id_shop
        );
    }

    /**
     * @throws MollieException
     */
    public function sendSubscriptionCancelWarningEmail(int $recurringOrderId): void
    {
        $data = $this->generalSubscriptionMailDataProvider->run($recurringOrderId);
        $subscriptionLanguage = new Language($data->getLangId());

        Mail::Send(
            $data->getLangId(),
            'mollie_subscription_cancel',
            sprintf($this->module->l('Your payment for the subscription of %s failed', self::FILE_NAME, $subscriptionLanguage->locale), $data->getProductName()),
            $data->toArray(),
            $data->getCustomerEmail(),
            implode(' ', [$data->getFirstName(), $data->getLastName()]),
            null,
            null,
            null,
            null,
            $this->module->getLocalPath() . 'mails/',
            false,
            $data->getShopId()
        );
    }

    /**
     * @throws MollieException
     */
    public function sendSubscriptionPaymentFailWarningMail(int $recurringOrderId): void
    {
        $data = $this->generalSubscriptionMailDataProvider->run($recurringOrderId);
        $subscriptionLanguage = new Language($data->getLangId());

        Mail::Send(
            $data->getLangId(),
            'mollie_subscription_payment_failed',
            sprintf($this->module->l('Your subscription for %s cancelled', self::FILE_NAME, $subscriptionLanguage->locale), $data->getProductName()),
            $data->toArray(),
            $data->getCustomerEmail(),
            implode(' ', [$data->getFirstName(), $data->getLastName()]),
            null,
            null,
            null,
            null,
            $this->module->getLocalPath() . 'mails/',
            false,
            $data->getShopId()
        );
    }

    /**
     * @throws MollieException
     */
    public function sendSubscriptionCarrierUpdateMail(int $recurringOrderId): bool
    {
        $data = $this->generalSubscriptionMailDataProvider->run($recurringOrderId);
        $subscriptionLanguage = new Language($data->getLangId());

        $result = Mail::Send(
            $data->getLangId(),
            'mollie_subscription_carrier_update',
            sprintf($this->module->l('Your subscription for %s carrier was updated', self::FILE_NAME, $subscriptionLanguage->locale), $data->getProductName()),
            $data->toArray(),
            $data->getCustomerEmail(),
            implode(' ', [$data->getFirstName(), $data->getLastName()]),
            null,
            null,
            null,
            null,
            $this->module->getLocalPath() . 'mails/',
            false,
            $data->getShopId()
        );

        return !(is_bool($result) && !$result);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    private function getOrderConfData(Order $order)
    {
        $virtual_product = true;
        $carrier = new Carrier($order->id_carrier);
        $customer = $order->getCustomer();

        $product_var_tpl_list = [];
        foreach ($order->getProducts() as $product) {
            $specific_price = null;
            /* @phpstan-ignore-next-line */
            $price = Product::getPriceStatic((int) $product['id_product'], false, ($product['product_attribute_id'] ? (int) $product['product_attribute_id'] : null), 6, null, false, true, $product['product_quantity'], false, (int) $order->id_customer, (int) $order->id_cart, (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}, $specific_price, true, true, null, true, $product['id_customization']);
            /* @phpstan-ignore-next-line */
            $price_wt = Product::getPriceStatic((int) $product['id_product'], true, ($product['product_attribute_id'] ? (int) $product['product_attribute_id'] : null), 2, null, false, true, $product['product_quantity'], false, (int) $order->id_customer, (int) $order->id_cart, (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')}, $specific_price, true, true, null, true, $product['id_customization']);

            $product_price = PS_TAX_EXC == Product::getTaxCalculationMethod() ? Tools::ps_round($price, 2) : $price_wt;

            $product_var_tpl = [
                'id_product' => $product['id_product'],
                'reference' => $product['reference'],
                'name' => $product['product_name'],
                'price' => $this->tools->displayPrice($product_price * $product['product_quantity'], $this->context->currency),
                'quantity' => $product['product_quantity'],
                'customization' => [],
            ];

            if (isset($product['price']) && $product['price']) {
                $product_var_tpl['unit_price'] = $this->tools->displayPrice($product_price, $this->context->currency);
                $product_var_tpl['unit_price_full'] = $this->tools->displayPrice($product_price, $this->context->currency)
                    . ' ' . $product['unity'];
            } else {
                $product_var_tpl['unit_price'] = $product_var_tpl['unit_price_full'] = '';
            }

            /* @phpstan-ignore-next-line */
            $customized_datas = Product::getAllCustomizedDatas((int) $order->id_cart, null, true, null, (int) $product['id_customization']);
            if (isset($customized_datas[$product['id_product']][$product['product_attribute_id']])) {
                $product_var_tpl['customization'] = [];
                foreach ($customized_datas[$product['id_product']][$product['product_attribute_id']][$order->id_address_delivery] as $customization) {
                    $customization_text = '';
                    if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                        foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                            $customization_text .= '<strong>' . $text['name'] . '</strong>: ' . $text['value'] . '<br />';
                        }
                    }

                    if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                        $customization_text .= Context::getContext()->getTranslator()->trans('%d image(s)', [count($customization['datas'][Product::CUSTOMIZE_FILE])], 'Admin.Payment.Notification') . '<br />';
                    }

                    $customization_quantity = (int) $customization['quantity'];

                    $product_var_tpl['customization'][] = [
                        'customization_text' => $customization_text,
                        'customization_quantity' => $customization_quantity,
                        'quantity' => $this->tools->displayPrice($customization_quantity * $product_price, $this->context->currency),
                    ];
                }
            }

            $product_var_tpl_list[] = $product_var_tpl;
            // Check if is not a virutal product for the displaying of shipping
            if (!$product['is_virtual']) {
                $virtual_product &= false;
            }
        }

        $invoice = new Address((int) $order->id_address_invoice);
        $delivery = new Address((int) $order->id_address_delivery);
        $delivery_state = $delivery->id_state ? new State((int) $delivery->id_state) : false;
        $invoice_state = $invoice->id_state ? new State((int) $invoice->id_state) : false;

        $product_list_txt = '';
        $product_list_html = '';
        if (count($product_var_tpl_list) > 0) {
            $product_list_txt = $this->getEmailTemplateContent('order_conf_product_list.txt', Mail::TYPE_TEXT, $product_var_tpl_list);
            $product_list_html = $this->getEmailTemplateContent('order_conf_product_list.tpl', Mail::TYPE_HTML, $product_var_tpl_list);
        }

        $cart_rules_list = $this->getCartRuleList($order, PS_TAX_EXC == Product::getTaxCalculationMethod());
        $cart_rules_list_txt = '';
        $cart_rules_list_html = '';
        if (count($cart_rules_list) > 0) {
            $cart_rules_list_txt = $this->getEmailTemplateContent('order_conf_cart_rules.txt', Mail::TYPE_TEXT, $cart_rules_list);
            $cart_rules_list_html = $this->getEmailTemplateContent('order_conf_cart_rules.tpl', Mail::TYPE_HTML, $cart_rules_list);
        }

        return [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{delivery_block_txt}' => $this->_getFormatedAddress($delivery, "\n"),
            '{invoice_block_txt}' => $this->_getFormatedAddress($invoice, "\n"),
            '{delivery_block_html}' => $this->_getFormatedAddress($delivery, '<br />', [
                'firstname' => '<span style="font-weight:bold;">%s</span>',
                'lastname' => '<span style="font-weight:bold;">%s</span>',
            ]),
            '{invoice_block_html}' => $this->_getFormatedAddress($invoice, '<br />', [
                'firstname' => '<span style="font-weight:bold;">%s</span>',
                'lastname' => '<span style="font-weight:bold;">%s</span>',
            ]),
            '{delivery_company}' => $delivery->company,
            '{delivery_firstname}' => $delivery->firstname,
            '{delivery_lastname}' => $delivery->lastname,
            '{delivery_address1}' => $delivery->address1,
            '{delivery_address2}' => $delivery->address2,
            '{delivery_city}' => $delivery->city,
            '{delivery_postal_code}' => $delivery->postcode,
            '{delivery_country}' => $delivery->country,
            '{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
            '{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
            '{delivery_other}' => $delivery->other,
            '{invoice_company}' => $invoice->company,
            '{invoice_vat_number}' => $invoice->vat_number,
            '{invoice_firstname}' => $invoice->firstname,
            '{invoice_lastname}' => $invoice->lastname,
            '{invoice_address2}' => $invoice->address2,
            '{invoice_address1}' => $invoice->address1,
            '{invoice_city}' => $invoice->city,
            '{invoice_postal_code}' => $invoice->postcode,
            '{invoice_country}' => $invoice->country,
            '{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
            '{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
            '{invoice_other}' => $invoice->other,
            '{order_name}' => $order->getUniqReference(),
            '{date}' => $this->tools->displayDate(date('Y-m-d H:i:s'), true),
            '{carrier}' => ($virtual_product || !isset($carrier->name)) ? $this->module->l('No carrier', self::FILE_NAME) : $carrier->name,
            '{payment}' => Tools::substr($order->payment, 0, 255),
            '{products}' => $product_list_html,
            '{products_txt}' => $product_list_txt,
            '{discounts}' => $cart_rules_list_html,
            '{discounts_txt}' => $cart_rules_list_txt,
            '{total_paid}' => $this->tools->displayPrice($order->total_paid, $this->context->currency),
            '{total_products}' => $this->tools->displayPrice(PS_TAX_EXC == Product::getTaxCalculationMethod() ? $order->total_products : $order->total_products_wt, $this->context->currency),
            '{total_discounts}' => $this->tools->displayPrice($order->total_discounts, $this->context->currency),
            '{total_shipping}' => $this->tools->displayPrice($order->total_shipping, $this->context->currency),
            '{total_wrapping}' => $this->tools->displayPrice($order->total_wrapping, $this->context->currency),
            '{total_tax_paid}' => $this->tools->displayPrice(($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl), $this->context->currency),
        ];
    }

    /**
     * @param bool $isTaxExcluded
     */
    private function getCartRuleList(Order $order, $isTaxExcluded)
    {
        $cartRulesList = [];
        $valueKey = $isTaxExcluded ? 'value_tax_excl' : 'value';

        foreach ($order->getCartRules() as $orderCartRule) {
            $cartRulesList[] = [
                'voucher_name' => $orderCartRule['name'],
                'voucher_reduction' => (0.00 != $orderCartRule['value'] ? '-' : '') . $this->tools->displayPrice($orderCartRule[$valueKey], $this->context->currency),
            ];
        }

        return $cartRulesList;
    }

    private function getFileAttachment($orderStatusId, Order $order)
    {
        $order_status = new OrderState((int) $orderStatusId, (int) $this->context->language->id);

        // Join PDF invoice
        if ((int) Configuration::get('PS_INVOICE') && $order_status->invoice && $order->invoice_number) {
            $fileAttachment = [];
            $order_invoice_list = $order->getInvoicesCollection();
            Hook::exec('actionPDFInvoiceRender', ['order_invoice_list' => $order_invoice_list]);
            $pdf = new PDF($order_invoice_list, PDF::TEMPLATE_INVOICE, $this->context->smarty);
            $fileAttachment['content'] = $pdf->render(false);
            $fileAttachment['name'] = Configuration::get('PS_INVOICE_PREFIX', (int) $order->id_lang, null, $order->id_shop) . sprintf('%06d', $order->invoice_number) . '.pdf';
            $fileAttachment['mime'] = 'application/pdf';
        } else {
            $fileAttachment = null;
        }

        return $fileAttachment;
    }

    private function getEmailTemplateContent($template_name, $mail_type, $var)
    {
        $email_configuration = Configuration::get('PS_MAIL_TYPE');
        if ($email_configuration != $mail_type && Mail::TYPE_BOTH != $email_configuration) {
            return '';
        }

        $pathToFindEmail = [
            _PS_THEME_DIR_ . 'mails' . DIRECTORY_SEPARATOR . $this->context->language->iso_code . DIRECTORY_SEPARATOR . $template_name,
            _PS_THEME_DIR_ . 'mails' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . $template_name,
            _PS_MAIL_DIR_ . $this->context->language->iso_code . DIRECTORY_SEPARATOR . $template_name,
            _PS_MAIL_DIR_ . 'en' . DIRECTORY_SEPARATOR . $template_name,
            _PS_MAIL_DIR_ . '_partials' . DIRECTORY_SEPARATOR . $template_name,
        ];

        foreach ($pathToFindEmail as $path) {
            if (Tools::file_exists_cache($path)) {
                $this->context->smarty->assign('list', $var);

                return $this->context->smarty->fetch($path);
            }
        }

        return '';
    }

    private function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = [])
    {
        return AddressFormat::generateAddress($the_address, ['avoid' => []], $line_sep, ' ', $fields_style);
    }

    /**
     * Sends a failed payment notification email to the customer
     *
     * @param Customer|null $customer The customer to send the email to (uses context if null)
     *
     * @return bool Whether the email was sent successfully
     *
     * @throws \PrestaShopException
     */
    public function sendFailedPaymentMail(?Customer $customer = null): bool
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        if (!Configuration::get(Config::MOLLIE_MAIL_WHEN_FAILED)) {
            $logger->debug(sprintf('%s - Payment failure email is disabled. Not sending email.', self::FILE_NAME));

            return false;
        }

        /** @var \Shop $shop */
        $shop = $this->context->shop;

        if (!$customer) {
            $customer = $this->context->customer;
        }

        if (empty($customer->email) || !\Validate::isEmail($customer->email)) {
            throw new \PrestaShopException('Failed to load customer email address');
        }

        if (!\Validate::isLoadedObject($customer)) {
            throw new \PrestaShopException('Failed to load customer object');
        }

        if (!\Validate::isLoadedObject($shop)) {
            throw new \PrestaShopException('Failed to load shop object');
        }

        $checkoutUrl = $this->context->link->getPageLink(
            'order',
            true,
            $this->context->language->id,
            [
                'step' => 3,
                'id_cart' => $this->context->cart->id,
            ]
        );

        $templateVars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{checkout_url}' => $checkoutUrl,
            '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
        ];

        return Mail::send(
            $customer->id_lang,
            'mollie_payment_failed',
            Mail::l('Payment Failed'),
            $templateVars,
            $customer->email,
            null,
            null,
            null,
            null,
            null,
            $this->module->getLocalPath() . 'mails/',
            false,
            $shop->id
        );
    }
}
