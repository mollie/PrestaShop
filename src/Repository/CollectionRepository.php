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

namespace Mollie\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CollectionRepository implements ReadOnlyCollectionRepositoryInterface
{
    /**
     * @var string
     */
    private $fullyClassifiedClassName;

    public function __construct(string $fullyClassifiedClassName)
    {
        $this->fullyClassifiedClassName = $fullyClassifiedClassName;
    }

    public function findAllInCollection($langId = null): \PrestaShopCollection
    {
        return new \PrestaShopCollection($this->fullyClassifiedClassName, $langId);
    }

    /**
     * @param array $keyValueCriteria
     * @param int|null $langId
     *
     * @return \ObjectModel|null
     *
     * @throws \PrestaShopException
     */
    public function findOneBy(array $keyValueCriteria, $langId = null): ?\ObjectModel
    {
        $psCollection = new \PrestaShopCollection($this->fullyClassifiedClassName, $langId);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $first = $psCollection->getFirst();

        return false === $first ? null : $first;
    }
}
