<?php

namespace Mollie\Provider;

class EnvironmentVersionProvider implements EnvironmentVersionProviderInterface
{
	/**
	 * @return string
	 */
	public function getPrestashopVersion()
	{
		return _PS_VERSION_;
	}
}
