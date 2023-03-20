<?php

declare(strict_types=1);

namespace Mollie\Subscription\Controller\Symfony;

use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionFAQController extends AbstractSymfonyController
{
    private const FILE_NAME = 'SubscriptionFAQController';

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('@Modules/mollie/views/templates/admin/Subscription/subscriptions-faq.html.twig',
            [
                'subscriptionCreationTittle' => $this->module->l('Subscription creation', self::FILE_NAME),
                'subscriptionCreation' => $this->module->l('To create a subscription option for a product variation, assign it a Mollie subscription attribute.', self::FILE_NAME),
                'importantInformationTittle' => $this->module->l('IMPORTANT points', self::FILE_NAME),
                'importantInformation' => $this->module->l('When you add Mollie subscription attributes, make sure you always include "none" as a fallback.', self::FILE_NAME),
                'cartRuleTitle' => $this->module->l('Cart rules', self::FILE_NAME),
                'cartRule' => $this->module->l('A customer can\'t add a subscription item to the shopping cart if it already contains a non-subscription item.', self::FILE_NAME),
                'cartRule2' => $this->module->l('A customer can\'t add subscription items with different recurring periods to the same shopping cart.', self::FILE_NAME),
                'cartRule3' => $this->module->l('Do not use cart rules with subscription products as this will cause errors due to incorrect pricing.', self::FILE_NAME),
                'subscriptionOrderLogicTitle' => $this->module->l('Recurring order creation', self::FILE_NAME),
                'recurringOrderCreation' => $this->module->l('Mollie for Prestashop automatically creates a new order when the previous order is paid for.', self::FILE_NAME),
                'recurringOrderPrice' => $this->module->l('Recurring orders always use the product price that was specified when the related subscription was created.', self::FILE_NAME),
            ]);
    }
}
