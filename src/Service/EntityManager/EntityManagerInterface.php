<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

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
