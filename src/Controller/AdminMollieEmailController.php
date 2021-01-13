<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 */

namespace Mollie\Controller;

use Module;
use Mollie;
use Mollie\Service\MolliePaymentMailService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

class AdminMollieEmailController extends FrameworkBundleAdminController
{
	public function sendSecondChanceMessage($orderId, Request $request)
	{
		/** @var Mollie $mollie */
		$mollie = Module::getInstanceByName('mollie'); //Unable to get services without mollieContainer.

		/** @var MolliePaymentMailService $molliePaymentMailService */
		$molliePaymentMailService = $mollie->getMollieContainer(MolliePaymentMailService::class);
		$response = $molliePaymentMailService->sendSecondChanceMail($orderId);

		if (empty($response)) {
			$this->addFlash('error',
				$this->trans('Unexpected error occurred', 'Module.mollie')
			);
		} else {
			$this->addFlash($response['success'] ? 'success' : 'error',
				$response['message']
			);
		}

		return $this->redirectToRoute('admin_orders_index', $request->query->all());
	}
}
