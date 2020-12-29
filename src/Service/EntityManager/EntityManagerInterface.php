<?php

namespace Mollie\Service\EntityManager;

use ObjectModel;
use PrestaShopException;

interface EntityManagerInterface
{
	/**
	 * @param ObjectModel $model
	 *
	 * @throws PrestaShopException
	 */
	public function flush(ObjectModel $model);
}
