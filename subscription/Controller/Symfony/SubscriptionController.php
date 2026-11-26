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

declare(strict_types=1);

namespace Mollie\Subscription\Controller\Symfony;

use Exception;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Shop;
use Mollie\Config\Config;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Subscription\Exception\SubscriptionApiException;
use Mollie\Subscription\Filters\SubscriptionFilters;
use Mollie\Subscription\Grid\SubscriptionGridDefinitionFactory;
use Mollie\Subscription\Handler\SubscriptionCancellationHandler;
use Mollie\Subscription\Handler\UpdateSubscriptionCarrierHandler;
use Mollie\Utility\VersionUtility;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionController extends AbstractSymfonyController
{
    private const FILE_NAME = 'SubscriptionController';
    private const FAQ_FILE_NAME = 'SubscriptionFAQController';

    /** @var ?ContainerInterface */
    protected $container;

    /** @var ?Environment */
    public $twig;

    public function __construct(
        ?ContainerInterface $container = null,
        ?Environment $twig = null
    ) {
        $this->container = $container;
        $this->twig = $twig;

        parent::__construct();
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return Response
     */
    public function indexAction(SubscriptionFilters $filters, Request $request)
    {
        /** @var Shop $shop */
        $shop = $this->module->getService(Shop::class);

        if ($shop->getContext() !== \Shop::CONTEXT_SHOP) {
            if (!$this->get('session')->getFlashBag()->has('error')) {
                $this->addFlash('error', $this->module->l('Select the shop that you want to configure'));
            }

            return $this->renderTwig('@PrestaShop/Admin/layout.html.twig');
        }

        /** @var GridFactoryInterface $currencyGridFactory */
        $currencyGridFactory = $this->module->getService('subscription_grid_factory');
        $currencyGrid = $currencyGridFactory->getGrid($filters);

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            $formHandler = $this->get('subscription_options_form_handler')->getForm();
        } else {
            $formHandler = $this->get('subscription_options_form_handler_deprecated')->getForm();
        }

        return $this->renderTwig('@Modules/mollie/views/templates/admin/Subscription/subscriptions-grid.html.twig', [
            'currencyGrid' => $this->presentGrid($currencyGrid),
            'enableSidebar' => true,
            'subscriptionOptionsForm' => $formHandler->createView(),
            'translations' => [
                'subscription_options' => $this->module->l('Subscription options', self::FILE_NAME),
                'enable_subscription_service' => $this->module->l('Enable subscription service', self::FILE_NAME),
                'enable_subscription_service_help' => $this->module->l('Toggle this option to enable products to be bought as recurring items.', self::FILE_NAME),
                'carrier_warning' => $this->module->l('Carrier to use in subscription orders', self::FILE_NAME),
                'carrier_warning_text' => $this->module->l('WARNING: do not change selection after getting first subscription order.', self::FILE_NAME),
                'subscription_options_help_link_text' => $this->module->l('How to configure Mollie subscriptions on Prestashop?', self::FILE_NAME),
            ],
            'links' => [
                'help_url' => 'https://docs.mollie.com/docs/prestashop-manage-subscriptions',
            ],
            'faq' => $this->getFAQTexts(),
        ]);
    }

    /**
     * @AdminSecurity("is_granted('create', request.get('_legacy_controller'))")
     */
    public function submitOptionsAction(Request $request): RedirectResponse
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            /** @var FormHandlerInterface $formHandler */
            $formHandler = $this->get('subscription_options_form_handler');
        } else {
            /** @var FormHandlerInterface $formHandler */
            $formHandler = $this->get('subscription_options_form_handler_deprecated');
        }

        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash(
                'error',
                $this->module->l('Failed to save options. Try again or contact support.', self::FILE_NAME)
            );

            return $this->redirectToRoute('admin_subscription_index');
        }

        // NOTE: By default getting was throwing silented error
        $carrier = $form->getData()['carrier'] ?? $form->getData()['subscription_options']['carrier'];

        $this->updateSubscriptionCarrier($carrier);

        $formHandler->save($form->getData());

        $this->addFlash(
            'success',
            $this->module->l('Options saved successfully.', self::FILE_NAME)
        );

        return $this->redirectToRoute('admin_subscription_index');
    }

    private function updateSubscriptionCarrier(int $newCarrierId): void
    {
        /** @var ConfigurationAdapter $configuration */
        $configuration = $this->module->getService(ConfigurationAdapter::class);
        $oldCarrierId = $configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        if (empty($oldCarrierId) || empty($newCarrierId)) {
            $this->addFlash(
                'error',
                $this->module->l('Carrier not found', self::FILE_NAME)
            );
        }

        /** @var UpdateSubscriptionCarrierHandler $subscriptionCarrierUpdateHandler */
        $subscriptionCarrierUpdateHandler = $this->module->getService(UpdateSubscriptionCarrierHandler::class);

        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        $failedSubscriptionOrderIdsToUpdate = $subscriptionCarrierUpdateHandler->run($newCarrierId);

        if (!empty($failedSubscriptionOrderIdsToUpdate)) {
            $logger->error('Failed to update subscription carrier for all orders.', [
                'failed_subscription_order_ids' => json_encode($failedSubscriptionOrderIdsToUpdate),
            ]);
        }
    }

    /**
     * Provides filters functionality.
     *
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function searchAction(Request $request): RedirectResponse
    {
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        return $responseBuilder->buildSearchResponse(
            $this->get(SubscriptionGridDefinitionFactory::class),
            $request,
            SubscriptionGridDefinitionFactory::GRID_ID,
            'admin_subscription_index'
        );
    }

    /**
     * @AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", redirectRoute="admin_subscription_index")
     */
    public function cancelAction(int $subscriptionId): RedirectResponse
    {
        /** @var SubscriptionCancellationHandler $subscriptionCancellationHandler */
        $subscriptionCancellationHandler = $this->module->getService(SubscriptionCancellationHandler::class);

        try {
            $subscriptionCancellationHandler->handle($subscriptionId);
        } catch (SubscriptionApiException $e) {
            $this->addFlash('error', $this->getErrorMessage($e));

            return $this->redirectToRoute('admin_subscription_index');
        }

        $this->addFlash(
            'success',
            $this->module->l('Successfully canceled', self::FILE_NAME)
        );

        return $this->redirectToRoute('admin_subscription_index');
    }

    private function getErrorMessage(Exception $e): string
    {
        $errors = [];

        if ($e instanceof SubscriptionApiException) {
            $errors[SubscriptionApiException::class] = [
                SubscriptionApiException::CANCELLATION_FAILED => $this->module->l('Failed to cancel subscription', self::FILE_NAME),
            ];
        }

        return $this->getErrorMessageForException($e, $errors);
    }

    /**
     * For PS 9 compatibility
     *
     * @param string $view
     * @param array $parameters
     *
     * @return Response
     */
    private function renderTwig(string $view, array $parameters = [])
    {
        if (!$this->twig) {
            return $this->render($view, $parameters);
        }

        return new Response(
            $this->twig->render($view, $parameters)
        );
    }

    private function getFAQTexts(): array
    {
        return [
            'faqTitle' => $this->module->l('Subscription FAQ', 'mollie'),
            'subscriptionCreationTittle' => $this->module->l('Subscription creation', self::FAQ_FILE_NAME),
            'subscriptionCreation' => $this->module->l('To create a subscription option for a product variation, assign it a Mollie subscription attribute.', self::FAQ_FILE_NAME),
            'importantInformationTittle' => $this->module->l('IMPORTANT points', self::FAQ_FILE_NAME),
            'importantInformation' => $this->module->l('When you add Mollie subscription attributes, make sure you always include \'none\' as a fallback.', self::FAQ_FILE_NAME),
            'carrierInformationTitle' => $this->module->l('IMPORTANT subscription carrier points', self::FAQ_FILE_NAME),
            'carrierInformation1' => $this->module->l('Make sure to select the default carrier for recurring orders in the Subscription options at the top of this page.', self::FAQ_FILE_NAME),
            'carrierInformation2' => $this->module->l('Carrier should cover all supported shop regions.', self::FAQ_FILE_NAME),
            'carrierInformation3' => $this->module->l('Carrier cannot be changed after first subscription order is placed.', self::FAQ_FILE_NAME),
            'carrierInformation4' => $this->module->l('Selected carrier pricing/weight settings or carrier selection in Mollie should not change. If they do, subscription orders must be cancelled and carrier re-selected in module settings.', self::FAQ_FILE_NAME),
            'cartRuleTitle' => $this->module->l('Cart rules', self::FAQ_FILE_NAME),
            'cartRule1' => $this->module->l('Do not use cart rules with subscription products as this will cause errors due to incorrect pricing.', self::FAQ_FILE_NAME),
            'giftWrappingTitle' => $this->module->l('Gift wrapping', self::FAQ_FILE_NAME),
            'giftWrapping1' => $this->module->l('Gift wrapping feature is not supported for subscription orders.', self::FAQ_FILE_NAME),
            'subscriptionOrderLogicTitle' => $this->module->l('Recurring order creation', self::FAQ_FILE_NAME),
            'recurringOrderCreation' => $this->module->l('Mollie for Prestashop automatically creates a new order when the previous order is paid for.', self::FAQ_FILE_NAME),
            'recurringOrderPrice' => $this->module->l('Recurring orders always use the product price that was specified when the related subscription was created.', self::FAQ_FILE_NAME),
            'recurringOrderAPIChanges' => $this->module->l('Recurring order will override the “Method” payment setting and will be using Mollie’s Payment API.', self::FAQ_FILE_NAME),
        ];
    }
}
