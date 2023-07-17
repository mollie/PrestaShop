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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Controller\AbstractMollieController;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Repository\CurrencyRepositoryInterface;
use Mollie\Utility\NumberUtility;

class MollieAjaxModuleFrontController extends AbstractMollieController
{
    const FILE_NAME = 'ajax';

    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'getTotalCartPrice':
                $this->getTotalCartPrice();
                // no break
            case 'displayCheckoutError':
                $this->displayCheckoutError();
        }
    }

    private function getTotalCartPrice()
    {
        $cart = Context::getContext()->cart;

        /** @var ToolsAdapter $tools */
        $tools = $this->module->getMollieContainer(ToolsAdapter::class);

        $paymentMethodId = (int) $tools->getValue('paymentMethodId');

        if (!$paymentMethodId) {
            $errorData = [
                'error' => true,
                'message' => 'Failed to get payment method ID.',
            ];

            $this->returnDefaultOrderSummaryBlock($cart, $errorData);
        }

        $molPaymentMethod = new MolPaymentMethod($paymentMethodId);

        if (!$molPaymentMethod->id) {
            $errorData = [
                'error' => true,
                'message' => 'Failed to find payment method.',
            ];

            $this->returnDefaultOrderSummaryBlock($cart, $errorData);
        }

        /** @var CurrencyRepositoryInterface $currencyRepository */
        $currencyRepository = $this->module->getMollieContainer(CurrencyRepositoryInterface::class);

        /** @var Currency $cartCurrency */
        $cartCurrency = $currencyRepository->findOneBy([
            'id_currency' => $cart->id_currency,
        ]);

        /** @var PaymentFeeProviderInterface $paymentFeeProvider */
        $paymentFeeProvider = $this->module->getMollieContainer(PaymentFeeProviderInterface::class);

        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->module->getMollieContainer(ConfigurationAdapter::class);

        try {
            $paymentFeeData = $paymentFeeProvider->getPaymentFee($molPaymentMethod, (float) $cart->getOrderTotal());
        } catch (FailedToProvidePaymentFeeException $exception) {
            $errorData = [
                'error' => true,
                'message' => 'Failed to get payment fee data.',
            ];

            $this->returnDefaultOrderSummaryBlock($cart, $errorData);

            exit;
        }

        $orderTotalWithTax = NumberUtility::plus($paymentFeeData->getPaymentFeeTaxIncl(), $cart->getOrderTotal());

        $orderTotalWithoutTax = NumberUtility::plus($paymentFeeData->getPaymentFeeTaxExcl(), $cart->getOrderTotal(false));

        $orderTotalTax = NumberUtility::minus($orderTotalWithTax, $orderTotalWithoutTax);

        $taxConfiguration = new TaxConfiguration();
        $presentedCart = $this->cart_presenter->present($cart);

        $presentedCart['totals'] = [
            'total' => [
                'type' => 'total',
                'label' => $this->module->l('Total', self::FILE_NAME),
                'amount' => $taxConfiguration->includeTaxes() ? $orderTotalWithTax : $orderTotalWithoutTax,
                'value' => $this->context->getCurrentLocale()->formatPrice(
                    $taxConfiguration->includeTaxes() ? $orderTotalWithTax : $orderTotalWithoutTax,
                    $cartCurrency->iso_code
                ),
            ],
            'total_including_tax' => [
                'type' => 'total',
                'label' => $this->module->l('Total (tax incl.)', self::FILE_NAME),
                'amount' => $orderTotalWithTax,
                'value' => $this->context->getCurrentLocale()->formatPrice(
                    $orderTotalWithTax,
                    $cartCurrency->iso_code
                ),
            ],
            'total_excluding_tax' => [
                'type' => 'total',
                'label' => $this->module->l('Total (tax excl.)', self::FILE_NAME),
                'amount' => $orderTotalWithoutTax,
                'value' => $this->context->getCurrentLocale()->formatPrice(
                    $orderTotalWithoutTax,
                    $cartCurrency->iso_code
                ),
            ],
        ];

        if (!$configuration->get('PS_TAX_DISPLAY')) {
            $this->returnDefaultOrderSummaryBlock($cart, [], $presentedCart);
        }

        $presentedCart['subtotals'] = [
            'tax' => [
                'type' => 'tax',
                'label' => $taxConfiguration->includeTaxes()
                    ? $this->translator->trans('Included taxes', [], 'Shop.Theme.Checkout')
                    : $this->translator->trans('Taxes', [], 'Shop.Theme.Checkout'),
                'amount' => $orderTotalTax,
                'value' => $this->context->getCurrentLocale()->formatPrice(
                    $orderTotalTax,
                    $cartCurrency->iso_code
                ),
            ],
        ];

        $this->returnDefaultOrderSummaryBlock($cart, [], $presentedCart);
    }

    private function displayCheckoutError()
    {
        $errorMessages = explode('#', Tools::getValue('hashTag'));

        foreach ($errorMessages as $errorMessage) {
            if (0 === strpos($errorMessage, 'mollieMessage=')) {
                $errorMessage = str_replace(
                    ['mollieMessage=', '_'],
                    ['', ' '],
                    $errorMessage
                );

                $this->context->smarty->assign([
                    'errorMessage' => $errorMessage,
                ]);

                $this->ajaxRender($this->context->smarty->fetch("{$this->module->getLocalPath()}views/templates/front/mollie_error.tpl"));
            }
        }

        exit;
    }

    private function returnDefaultOrderSummaryBlock(Cart $cart, array $errorData = [], array $presentedCart = null)
    {
        if (!$presentedCart) {
            $presentedCart = $this->cart_presenter->present($cart);
        }

        if (empty($errorData)) {
            $errorData['error'] = false;
        }

        $this->context->smarty->assign(array_merge([
                'configuration' => $this->getTemplateVarConfiguration(),
                'cart' => $presentedCart,
                'display_transaction_updated_info' => Tools::getIsset('updatedTransaction'),
            ], $errorData)
        );

        $this->ajaxRender(
            json_encode(
                [
                    'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
                ]
            )
        );
    }
}
