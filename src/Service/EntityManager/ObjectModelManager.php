<?php

namespace Mollie\Service\EntityManager;

use ObjectModel;

class ObjectModelManager implements EntityManagerInterface
{
	/**
	 * @param ObjectModel $model
	 *
	 * @throws \PrestaShopException
	 */
	public function flush(ObjectModel $model)
	{
		$model->save();
	}
}
