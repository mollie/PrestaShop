<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 */

namespace Mollie\Controller;

use Module;
use Mollie;
use Mollie\Service\MolliePaymentMailService;
use Symfony\Component\HttpFoundation\Request;

class AdminMollieEmailController extends \PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController
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
