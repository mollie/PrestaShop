<?php

declare(strict_types=1);

namespace Mollie\Subscription\Controller\Symfony;

use Exception;
use Mollie\Subscription\Exception\SubscriptionApiException;
use Mollie\Subscription\Filters\SubscriptionFilters;
use Mollie\Subscription\Grid\SubscriptionGridDefinitionFactory;
use Mollie\Subscription\Handler\SubscriptionCancellationHandler;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractSymfonyController
{
    private const FILE_NAME = 'SubscriptionController';

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param SubscriptionFilters $filters
     *
     * @return Response
     */
    public function indexAction(SubscriptionFilters $filters, Request $request)
    {
        /** @var GridFactoryInterface $currencyGridFactory */
        $currencyGridFactory = $this->leagueContainer->getService('subscription_grid_factory');
        $currencyGrid = $currencyGridFactory->getGrid($filters);

        return $this->render('@Modules/mollie/views/templates/admin/Subscription/subscriptions-grid.html.twig', [
            'currencyGrid' => $this->presentGrid($currencyGrid),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    /**
     * Provides filters functionality.
     *
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param Request $request
     *
     * @return RedirectResponse
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
     *
     * @param int $subscriptionId
     *
     * @return RedirectResponse
     */
    public function cancelAction(int $subscriptionId): RedirectResponse
    {
        /** @var SubscriptionCancellationHandler $subscriptionCancellationHandler */
        $subscriptionCancellationHandler = $this->leagueContainer->getService(SubscriptionCancellationHandler::class);
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
}
