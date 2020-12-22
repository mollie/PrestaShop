<?php

namespace Mollie\Utility\Decoder;

interface DecoderInterface
{
	/**
	 * @param string $encodedElement
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function decode($encodedElement, $params = []);
}
