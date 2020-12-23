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
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Grid\Definition\Modifier;

use Mollie;
use Mollie\Grid\Action\Type\SecondChanceRowAction;
use Mollie\Grid\Row\AccessibilityChecker\SecondChanceAccessibilityChecker;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

class OrderGridDefinitionModifier implements GridDefinitionModifierInterface
{
	private $module;

	public function __construct(Mollie $module)
	{
		$this->module = $module;
	}

	/**
	 * {@inheritDoc}
	 */
	public function modify(GridDefinitionInterface $gridDefinition)
	{
		$translator = $this->module->getTranslator();

		$gridDefinition->getColumns()
			->addBefore('date_add', (new ActionColumn('second_chance'))
				->setName($translator->trans('Resend payment link', [], 'Modules.mollie'))
				->setOptions([
					'actions' => (new RowActionCollection())
						->add((new SecondChanceRowAction('transaction_id'))
							->setName($translator->trans('You will resend email with payment link to the customer', [], 'Modules.mollie'))
							->setOptions([
								'route' => Mollie\Config\Config::ROUTE_RESEND_SECOND_CHANCE_PAYMENT_MESSAGE,
								'route_param_field' => 'id_order',
								'route_param_name' => 'orderId',
								'use_inline_display' => true,
								'accessibility_checker' => $this->module->getMollieContainer(
									SecondChanceAccessibilityChecker::class
								),
							])
						),
				])
			);
	}
}
