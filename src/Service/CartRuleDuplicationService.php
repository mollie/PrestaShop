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

namespace Mollie\Service;

use CartRule;
use Context;
use Mollie\Repository\CartRuleRepository;

class CartRuleDuplicationService
{
	/**
	 * @var CartRuleRepository
	 */
	private $cartRuleRepository;

	public function __construct(CartRuleRepository $cartRuleRepository)
	{
		$this->cartRuleRepository = $cartRuleRepository;
	}

	/**
	 * @param array $cartRules
	 *
	 * @return bool
	 *
	 * @throws \PrestaShopException
	 */
	public function restoreCartRules($cartRules = [])
	{
		if (empty($cartRules)) {
			return true;
		}
		$context = Context::getContext();

		foreach ($cartRules as $cartRuleContent) {
			/** @var CartRule $cartRule */
			$cartRule = $this->cartRuleRepository->findOneBy(['id_cart_rule' => (int) $cartRuleContent['id_cart_rule']]);

			if ($cartRule->checkValidity($context, false, false)) {
				$context->cart->addCartRule($cartRule->id);
			}
		}

		return true;
	}
}
