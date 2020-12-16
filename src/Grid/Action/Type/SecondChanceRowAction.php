<?php
/**
 * 2007-2017 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Mollie\Grid\Action\Type;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\AbstractRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SecondChanceRowAction extends AbstractRowAction
{
	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return 'second_chance';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureOptions(OptionsResolver $resolver)
	{
		/*
		 * options passed to the resolver will be available in the Grid Row action
		 * and also in the template responsible of rendering the action.
		 */

		$resolver
			->setRequired([
				'route',
				'route_param_name',
				'route_param_field',
			])
			->setDefaults([
				'use_inline_display' => false,
				'accessibility_checker' => null,
			])
			->setAllowedTypes('route', 'string')
			->setAllowedTypes('route_param_name', 'string')
			->setAllowedTypes('route_param_field', 'string')
			->setAllowedTypes('accessibility_checker', [AccessibilityCheckerInterface::class, 'callable', 'null'])
			->setAllowedTypes('use_inline_display', 'bool')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isApplicable(array $record)
	{
		$accessibilityChecker = $this->getOptions()['accessibility_checker'];

		if ($accessibilityChecker instanceof AccessibilityCheckerInterface) {
			return $accessibilityChecker->isGranted($record);
		}

		if (is_callable($accessibilityChecker)) {
			return call_user_func($accessibilityChecker, $record);
		}

		return parent::isApplicable($record);
	}
}
