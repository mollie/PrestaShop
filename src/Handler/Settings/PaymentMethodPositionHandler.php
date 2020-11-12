<?php

namespace Mollie\Handler\Settings;

use Mollie\Repository\PaymentMethodRepositoryInterface;

final class PaymentMethodPositionHandler implements PaymentMethodPositionHandlerInterface
{
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * @param array $positions
     *
     * @return mixed|void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function savePositions(array $positions)
    {
        $ids = array_keys($positions);

        if (empty($ids)) {
            return;
        }

        /** @var \MolPaymentMethod[] $paymentMethods */
        $paymentMethods = $this->paymentMethodRepository
            ->findAll()
            ->where('id_payment_method', 'in', $ids)
        ;

        foreach ($paymentMethods as $paymentMethod) {
            $position = $positions[$paymentMethod->id];

            $paymentMethod->position = $position;

            $paymentMethod->update();
        }
    }
}
