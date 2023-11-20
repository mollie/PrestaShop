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

if (!defined('_PS_VERSION_')) {
    exit;
}

interface EntityManagerInterface
{
    /**
     * @throws PrestaShopException
     */
    public function flush(ObjectModel $model);
}
