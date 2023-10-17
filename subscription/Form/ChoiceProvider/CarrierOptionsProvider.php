<?php

namespace Mollie\Subscription\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;

class CarrierOptionsProvider implements FormChoiceProviderInterface
{
    // TODO implement

    public function getChoices(): array
    {
        $choices[1] = 'test-carrier';

        return $choices;
    }
}
