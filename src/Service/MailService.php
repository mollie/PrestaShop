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

namespace Mollie\Service;

use Address;
use AddressFormat;
use AttributeCore as Attribute;
use Carrier;
use CartRule;
use Configuration;
use Context;
use Customer;
use Hook;
use Language;
use Mail;
use Module;
use Mollie;
use Mollie\Config\Config;
use Order;
use OrderState;
use PDF;
use Product;
use State;
use Tools;

class MailService
{
	const FILE_NAME = 'MailService';

	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var Context
	 */
	private $context;

	public function __construct(Mollie $module)
	{
		$this->module = $module;
		$this->context = Context::getContext();
	}

	public function sendSecondChanceMail(Customer $customer, $checkoutUrl, $methodName, $shopId)
	{
		Mail::send(
			$customer->id_lang,
			'mollie_payment',
			Mail::l('Order payment'),
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
	 * @param Order $order
	 * @param int $orderStateId
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	public function sendOrderConfMail(Order $order, $orderStateId)
	{
		$data = $this->getOrderConfData($order, $orderStateId);
		$fileAttachment = $this->getFileAttachment($orderStateId, $order);
		$customer = $order->getCustomer();
		Mail::Send(
			(int) $order->id_lang,
			'order_conf',
			Mail::l('Order confirmation', (int) $order->id_lang),
			$data,
			$customer->email,
			$customer->firstname . ' ' . $customer->lastname,
			null,
			null,
			$fileAttachment,
			null, _PS_MAIL_DIR_, false, (int) $order->id_shop
		);
	}

	/**
	 * @param Order $order
	 * @param int $orderStateId
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	public function sendNewOrderMail(Order $order, $orderStateId)
	{
		if (!Module::isEnabled(Config::EMAIL_ALERTS_MODULE_NAME)) {
			return;
		}
		$customer = $order->getCustomer();

		/** @var \Ps_EmailAlerts $emailAlertsModule */
		$emailAlertsModule = Module::getInstanceByName(Config::EMAIL_ALERTS_MODULE_NAME);

		$emailAlertsModule->hookActionValidateOrder(
			[
				'currency' => $this->context->currency,
				'order' => $order,
				'customer' => $customer,
				'cart' => $this->context->cart,
				'orderStatus' => new OrderState($orderStateId),
			]
		);
	}

	/**
	 * @param Order $order
	 * @param int $orderStateId
	 *
	 * @return array
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
	 */
	private function getOrderConfData(Order $order, $orderStateId)
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

			$attribute = new Attribute($product['product_attribute_id'], $this->context->language->id);
			$product_var_tpl = [
				'id_product' => $product['id_product'],
				'reference' => $product['reference'],
				'name' => $product['product_name'] . (\Validate::isLoadedObject($attribute) ? ' - ' . $attribute->name : ''),
				'price' => Tools::displayPrice($product_price * $product['product_quantity'], $this->context->currency, false),
				'quantity' => $product['product_quantity'],
				'customization' => [],
			];

			if (isset($product['price']) && $product['price']) {
				$product_var_tpl['unit_price'] = Tools::displayPrice($product_price, $this->context->currency, false);
				$product_var_tpl['unit_price_full'] = Tools::displayPrice($product_price, $this->context->currency, false)
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
						Config::isVersion17() ?
							/* @phpstan-ignore-next-line */
							$customization_text .= Context::getContext()->getTranslator()->trans('%d image(s)', [count($customization['datas'][Product::CUSTOMIZE_FILE])], 'Admin.Payment.Notification') . '<br />'
							:
							/* @phpstan-ignore-next-line */
							$customization_text .= sprintf(Tools::displayError('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])) . '<br />';
					}

					$customization_quantity = (int) $customization['quantity'];

					$product_var_tpl['customization'][] = [
						'customization_text' => $customization_text,
						'customization_quantity' => $customization_quantity,
						'quantity' => Tools::displayPrice($customization_quantity * $product_price, $this->context->currency, false),
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

		$cart_rules_list = $this->getCartRuleList($order, $orderStateId);
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
			'{date}' => Tools::displayDate(date('Y-m-d H:i:s'), null, true),
			'{carrier}' => ($virtual_product || !isset($carrier->name)) ? $this->module->l('No carrier', self::FILE_NAME) : $carrier->name,
			'{payment}' => Tools::substr($order->payment, 0, 255),
			'{products}' => $product_list_html,
			'{products_txt}' => $product_list_txt,
			'{discounts}' => $cart_rules_list_html,
			'{discounts_txt}' => $cart_rules_list_txt,
			'{total_paid}' => Tools::displayPrice($order->total_paid, $this->context->currency, false),
			'{total_products}' => Tools::displayPrice(PS_TAX_EXC == Product::getTaxCalculationMethod() ? $order->total_products : $order->total_products_wt, $this->context->currency, false),
			'{total_discounts}' => Tools::displayPrice($order->total_discounts, $this->context->currency, false),
			'{total_shipping}' => Tools::displayPrice($order->total_shipping, $this->context->currency, false),
			'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $this->context->currency, false),
			'{total_tax_paid}' => Tools::displayPrice(($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl), $this->context->currency, false),
		];
	}

	private function getCartRuleList(Order $order, $orderStateId)
	{
		$customer = $order->getCustomer();
		$order_list = [];
		$cart_rules = $this->context->cart->getCartRules();
		$order_list[] = $order;
		$cart_rule_used = [];

		$cart_rules_list = [];
		$total_reduction_value_ti = 0;
		$total_reduction_value_tex = 0;
		foreach ($cart_rules as $cart_rule) {
			$package = ['id_carrier' => $order->id_carrier, 'id_address' => $order->id_address_delivery];
			$values = [
				'tax_incl' => $cart_rule['obj']->getContextualValue(true, $this->context, CartRule::FILTER_ACTION_ALL_NOCAP, $package),
				'tax_excl' => $cart_rule['obj']->getContextualValue(false, $this->context, CartRule::FILTER_ACTION_ALL_NOCAP, $package),
			];

			// If the reduction is not applicable to this order, then continue with the next one
			if (!$values['tax_excl']) {
				continue;
			}

			// IF
			//  This is not multi-shipping
			//  The value of the voucher is greater than the total of the order
			//  Partial use is allowed
			//  This is an "amount" reduction, not a reduction in % or a gift
			// THEN
			//  The voucher is cloned with a new value corresponding to the remainder
			if (1 == count($order_list) && $values['tax_incl'] > ($order->total_products_wt - $total_reduction_value_ti) && 1 == $cart_rule['obj']->partial_use && $cart_rule['obj']->reduction_amount > 0) {
				// Create a new voucher from the original
				$voucher = new CartRule((int) $cart_rule['obj']->id); // We need to instantiate the CartRule without lang parameter to allow saving it
				unset($voucher->id);

				// Set a new voucher code
				$voucher->code = empty($voucher->code) ? substr(md5($order->id . '-' . $order->id_customer . '-' . $cart_rule['obj']->id), 0, 16) : $voucher->code . '-2';
				if (preg_match('/\-([0-9]{1,2})\-([0-9]{1,2})$/', $voucher->code, $matches) && $matches[1] == $matches[2]) {
					$voucher->code = preg_replace('/' . $matches[0] . '$/', '-' . (intval($matches[1]) + 1), $voucher->code);
				}

				// Set the new voucher value
				if ($voucher->reduction_tax) {
					$voucher->reduction_amount = ($total_reduction_value_ti + $values['tax_incl']) - $order->total_products_wt;

					// Add total shipping amout only if reduction amount > total shipping
					if (1 == $voucher->free_shipping && $voucher->reduction_amount >= $order->total_shipping_tax_incl) {
						$voucher->reduction_amount -= $order->total_shipping_tax_incl;
					}
				} else {
					$voucher->reduction_amount = ($total_reduction_value_tex + $values['tax_excl']) - $order->total_products;

					// Add total shipping amout only if reduction amount > total shipping
					if (1 == $voucher->free_shipping && $voucher->reduction_amount >= $order->total_shipping_tax_excl) {
						$voucher->reduction_amount -= $order->total_shipping_tax_excl;
					}
				}
				if ($voucher->reduction_amount <= 0) {
					continue;
				}

				if ($customer->isGuest()) {
					$voucher->id_customer = 0;
				} else {
					$voucher->id_customer = $order->id_customer;
				}

				$voucher->quantity = 1;
				$voucher->reduction_currency = $order->id_currency;
				$voucher->quantity_per_user = 1;
				if ($voucher->add()) {
					// If the voucher has conditions, they are now copied to the new voucher
					CartRule::copyConditions($cart_rule['obj']->id, $voucher->id);
					$orderLanguage = new Language((int) $order->id_lang);

					$params = [
						'{voucher_amount}' => Tools::displayPrice($voucher->reduction_amount, $this->context->currency, false),
						'{voucher_num}' => $voucher->code,
						'{firstname}' => $customer->firstname,
						'{lastname}' => $customer->lastname,
						'{id_order}' => $order->reference,
						'{order_name}' => $order->getUniqReference(),
					];
					Mail::Send(
						(int) $order->id_lang,
						'voucher',
						$this->module->l(
							'New voucher for your order %s',
							self::FILE_NAME
						),
						$params,
						$customer->email,
						$customer->firstname . ' ' . $customer->lastname,
						null, null, null, null, _PS_MAIL_DIR_, false, (int) $order->id_shop
					);
				}

				$values['tax_incl'] = $order->total_products_wt - $total_reduction_value_ti;
				$values['tax_excl'] = $order->total_products - $total_reduction_value_tex;
				if (1 == $voucher->free_shipping) {
					$values['tax_incl'] += $order->total_shipping_tax_incl;
					$values['tax_excl'] += $order->total_shipping_tax_excl;
				}
			}
			$total_reduction_value_ti += $values['tax_incl'];
			$total_reduction_value_tex += $values['tax_excl'];

			$order->addCartRule($cart_rule['obj']->id, $cart_rule['obj']->name, $values, 0, $cart_rule['obj']->free_shipping);

			if ($orderStateId != Configuration::get('PS_OS_ERROR') && $orderStateId != Configuration::get('PS_OS_CANCELED')
				&& !in_array($cart_rule['obj']->id, $cart_rule_used)) {
				$cart_rule_used[] = $cart_rule['obj']->id;

				// Create a new instance of Cart Rule without id_lang, in order to update its quantity
				$cart_rule_to_update = new CartRule((int) $cart_rule['obj']->id);
				$cart_rule_to_update->quantity = max(0, $cart_rule_to_update->quantity - 1);
				$cart_rule_to_update->update();
			}

			$cart_rules_list[] = [
				'voucher_name' => $cart_rule['obj']->name,
				'voucher_reduction' => (0.00 != $values['tax_incl'] ? '-' : '') . Tools::displayPrice($values['tax_incl'], $this->context->currency, false),
			];
		}

		return $cart_rules_list;
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
}
