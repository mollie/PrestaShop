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

namespace Mollie\DTO\Object;

class Amount
{
	/**
	 * @var string
	 */
	private $currency;

	/**
	 * @var float
	 */
	private $value;

	public function __construct($currency, $value)
	{
		$this->currency = $currency;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return number_format($this->value, 2, '.', '');
	}

	/**
	 * @param float $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
}
