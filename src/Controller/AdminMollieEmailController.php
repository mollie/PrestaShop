<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Controller;

use Mollie;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\LoggerInterface;
use Mollie\Service\MolliePaymentMailService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieEmailController extends FrameworkBundleAdminController
{
    const FILE_NAME = 'AdminMollieEmailController';

    public function sendSecondChanceMessage($orderId, Request $request)
    {
        /** @var Mollie $mollie */
        $mollie = (new ModuleFactory())->getModule();

        /** @var LoggerInterface $logger */
        $logger = $mollie->getService(LoggerInterface::class);

        /** @var MolliePaymentMailService $molliePaymentMailService */
        $molliePaymentMailService = $mollie->getService(MolliePaymentMailService::class);

        $response = $molliePaymentMailService->sendSecondChanceMail($orderId);

        if (empty($response)) {
            $logger->error(sprintf('%s - Empty second change mail', self::FILE_NAME));

            $this->addFlash(
                'error',
                $this->trans('Unexpected error occurred', 'Module.mollie')
            );
        } else {
            $this->addFlash(
                $response['success'] ? 'success' : 'error',
                $response['message']
            );
        }

        return $this->redirectToRoute('admin_orders_index', $request->query->all());
    }
}
