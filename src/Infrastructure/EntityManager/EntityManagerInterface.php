<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Infrastructure\EntityManager;

interface EntityManagerInterface
{
    /**
     * @param \ObjectModel $model
     * @param string $unitOfWorkType - @see ObjectModelUnitOfWork
     * @param string|null $specificKey
     *
     * @return EntityManagerInterface
     */
    public function persist(
        \ObjectModel $model,
        string $unitOfWorkType,
        ?string $specificKey = null
    ): EntityManagerInterface;

    /**
     * @return array<\ObjectModel>
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function flush(): array;
}
