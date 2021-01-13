<?php

namespace Mollie\Utility\Decoder;

class JsonDecoder implements DecoderInterface
{
	/**
	 * @param string $encodedElement
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function decode($encodedElement, $params = [])
	{
		return json_decode($encodedElement, ...$params);
	}
}
