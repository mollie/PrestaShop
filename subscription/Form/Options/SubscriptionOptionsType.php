<?php

namespace Mollie\Subscription\Form\Options;

use Module;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SubscriptionOptionsType extends TranslatorAwareType
{
    /** @var FormChoiceProviderInterface */
    private $carrierOptionProvider;
    /** @var Module */
    private $module;

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        FormChoiceProviderInterface $carrierOptionProvider,
        Module $module
    ) {
        parent::__construct($translator, $locales);

        $this->carrierOptionProvider = $carrierOptionProvider;
        $this->module = $module;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('carrier', ChoiceType::class, [
                'required' => true,
                'choices' => $this->carrierOptionProvider->getChoices(),
// TODO structure should look like below visible => writable to configuration value
//         [
//            'Yes' => 'stock_yes',
//            'No' => 'stock_no',
//        ],
                // TODO migrate to modern translation system
                'placeholder' => $this->module->l('Choose your carrier'),
            ]);
    }
}
