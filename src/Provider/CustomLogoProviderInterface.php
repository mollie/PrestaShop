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

namespace Mollie\Provider;

use MolPaymentMethod;

interface CustomLogoProviderInterface
{
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getLocalPath();

	/**
	 * @return string
	 */
	public function getPathUri();

	/**
	 * @return string
	 */
	public function getLocalLogoPath();

	/**
	 * @return string
	 */
	public function getLogoPathUri();

	/**
	 * @return bool
	 */
	public function logoExists();

	/**
	 * @return string
	 */
	public function getMethodOptionLogo(MolPaymentMethod $methodObj);
}
