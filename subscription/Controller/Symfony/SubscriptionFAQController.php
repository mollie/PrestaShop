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
                'subscriptionCreation' => $this->module->l('To create subscription option for product you need to add subscription attribute for the products that you want to offer as subscriptions', self::FILE_NAME),
                'importantInformationTittle' => $this->module->l('IMPORTANT points', self::FILE_NAME),
                'importantInformation' => $this->module->l('If you are creating subscription product, make sure you create attribute with option subscription:none to avoid confusing warnings', self::FILE_NAME),
                'cartRUleTitle' => $this->module->l('Cart rules', self::FILE_NAME),
                'cartRUle' => $this->module->l('Order can have only 1 specific subscription product and it can\'t have any other product but it can have multiple quantities', self::FILE_NAME),
                'subscriptionOrderLogicTitle' => $this->module->l('Recurring order creation', self::FILE_NAME),
                'recurringOrderCreation' => $this->module->l('New order will be created when module gets mollie notification that subscription translation is paid', self::FILE_NAME),
                'recurringOrderPrice' => $this->module->l('Recurring order will always be using the same product price that was used when subscription was created', self::FILE_NAME),
            ]);
    }
}
