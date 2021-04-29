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

use Mollie\Utility\NumberUtility;
use PrestaShop\Decimal\DecimalNumber;

class MollieAjaxModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		$action = Tools::getValue('action');
		switch ($action) {
			case 'getTotalCartPrice':
				$cart = Context::getContext()->cart;
				$paymentFee = Tools::getValue('paymentFee');
				if (!$paymentFee) {
					$presentedCart = $this->cart_presenter->present($this->context->cart);
					$this->context->smarty->assign([
						'configuration' => $this->getTemplateVarConfiguration(),
						'cart' => $presentedCart,
						'display_transaction_updated_info' => Tools::getIsset('updatedTransaction'),
					]);

					$this->ajaxDie(
						json_encode(
							[
								'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
							]
						)
					);
				}

                $paymentFee = new DecimalNumber(Tools::getValue('paymentFee'));
                $orderTotal = new DecimalNumber((string) $cart->getOrderTotal());
				$orderTotalWithFee = NumberUtility::plus($paymentFee->toPrecision(2), $orderTotal->toPrecision(2));

				$orderTotalNoTax = new DecimalNumber((string) $cart->getOrderTotal(false));
				$orderTotalNoTaxWithFee = NumberUtility::plus($paymentFee->toPrecision(2), $orderTotalNoTax->toPrecision(2));

				$total_including_tax = $orderTotalWithFee;
				$total_excluding_tax = $orderTotalNoTaxWithFee;

				$taxConfiguration = new TaxConfiguration();
				$presentedCart = $this->cart_presenter->present($this->context->cart);

				$presentedCart['totals'] = [
					'total' => [
						'type' => 'total',
						'label' => $this->translator->trans('Total', [], 'Shop.Theme.Checkout'),
						'amount' => $taxConfiguration->includeTaxes() ? $total_including_tax : $total_excluding_tax,
						'value' => Tools::displayPrice(
							$taxConfiguration->includeTaxes() ? (float) $total_including_tax : (float) $total_excluding_tax
						),
					],
					'total_including_tax' => [
						'type' => 'total',
						'label' => $this->translator->trans('Total (tax incl.)', [], 'Shop.Theme.Checkout'),
						'amount' => $total_including_tax,
						'value' => Tools::displayPrice((float) $total_including_tax),
					],
					'total_excluding_tax' => [
						'type' => 'total',
						'label' => $this->translator->trans('Total (tax excl.)', [], 'Shop.Theme.Checkout'),
						'amount' => $total_excluding_tax,
						'value' => Tools::displayPrice((float) $total_excluding_tax),
					],
				];

				$this->context->smarty->assign([
					'configuration' => $this->getTemplateVarConfiguration(),
					'cart' => $presentedCart,
					'display_transaction_updated_info' => Tools::getIsset('updatedTransaction'),
				]);

				$this->ajaxDie(
					json_encode(
						[
							'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
						]
					)
				);
				break;
			case 'displayCheckoutError':
				$errorMessages = explode('#', Tools::getValue('hashTag'));
				foreach ($errorMessages as $errorMessage) {
					if (0 === strpos($errorMessage, 'mollieMessage=')) {
						$errorMessage = str_replace('mollieMessage=', '', $errorMessage);
						$errorMessage = str_replace('_', ' ', $errorMessage);
						$this->context->smarty->assign([
							'errorMessage' => $errorMessage,
						]);
						$this->ajaxDie($this->context->smarty->fetch("{$this->module->getLocalPath()}views/templates/front/mollie_error.tpl"));
					}
				}
				$this->ajaxDie();
		}
	}
}
