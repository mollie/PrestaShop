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

namespace Mollie\Verification\OrderTotal;

use Mollie\Exception\OrderTotalRestrictionException;
use Mollie\Repository\CurrencyRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use PrestaShopCollection;

class CanOrderTotalBeUpdated implements OrderTotalVerificationInterface
{
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @return bool
     *
     * @throws OrderTotalRestrictionException
     */
    public function verify()
    {
        if (!$this->hasCurrencies()) {
            throw new OrderTotalRestrictionException('Failed to refresh order total restriction values: None available currencies were found', OrderTotalRestrictionException::NO_AVAILABLE_CURRENCIES_FOUND);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function hasCurrencies()
    {
        /** @var PrestaShopCollection $currencies */
        $currencies = $this->currencyRepository->findAll();

        return (bool) $currencies->count();
    }
}
