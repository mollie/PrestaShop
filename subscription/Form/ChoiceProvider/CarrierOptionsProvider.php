<?php

namespace Mollie\Subscription\Form\ChoiceProvider;

use Mollie\Repository\CarrierRepositoryInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;

class CarrierOptionsProvider implements FormChoiceProviderInterface
{
    /** @var CarrierRepositoryInterface */
    private $carrierRepository;

    public function __construct(
        \Mollie $module
    ) {
        $this->carrierRepository = $module->getService(CarrierRepositoryInterface::class);
    }

    public function getChoices(): array
    {
        /** @var \Carrier[] $carriers */
        $carriers = $this->carrierRepository->findAllBy([
            'active' => 1,
            'deleted' => 0,
        ]);

        $choices = [];

        foreach ($carriers as $carrier) {
            $choices[$carrier->name] = (int) $carrier->id;
        }

        return $choices;
    }
}
