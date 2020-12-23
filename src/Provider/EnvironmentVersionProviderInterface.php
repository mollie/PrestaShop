<?php

namespace Mollie\Provider;

interface EnvironmentVersionProviderInterface
{
	/**
	 * @return string
	 */
	public function getPrestashopVersion();
}
