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

namespace Mollie\Shared\Infrastructure\Repository;

use Mollie\Shared\Infrastructure\Exception\CouldNotHandleAbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AbstractRepository implements ReadOnlyRepositoryInterface
{
    /**
     * @var string
     */
    private $fullyClassifiedClassName;

    public function __construct($fullyClassifiedClassName)
    {
        if (is_object($fullyClassifiedClassName)) {
            $this->fullyClassifiedClassName = get_class($fullyClassifiedClassName);

            return;
        }

        $this->fullyClassifiedClassName = $fullyClassifiedClassName;
    }

    /** {@inheritdoc} */
    public function findAll(int $langId = null): \PrestaShopCollection
    {
        return new \PrestaShopCollection($this->fullyClassifiedClassName, $langId);
    }

    /** {@inheritdoc} */
    public function findOneBy(array $keyValueCriteria, int $langId = null): ?\ObjectModel
    {
        $psCollection = new \PrestaShopCollection($this->fullyClassifiedClassName, $langId);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $first = $psCollection->getFirst();

        /* @phpstan-ignore-next-line */
        return false === $first ? null : $first;
    }

    /** {@inheritdoc} */
    public function findAllBy(array $keyValueCriteria, int $langId = null): ?\PrestaShopCollection
    {
        $psCollection = new \PrestaShopCollection($this->fullyClassifiedClassName, $langId);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $all = $psCollection->getAll();

        /* @phpstan-ignore-next-line */
        return false === $all ? null : $all;
    }

    /** {@inheritdoc} */
    public function findOrFail(array $keyValueCriteria, int $langId = null): \ObjectModel
    {
        try {
            $value = $this->findOneBy($keyValueCriteria, $langId);
        } catch (\Throwable $exception) {
            throw CouldNotHandleAbstractRepository::unknownError($exception);
        }

        if (!$value) {
            throw CouldNotHandleAbstractRepository::failedToFindRecord($this->fullyClassifiedClassName, $keyValueCriteria);
        }

        return $value;
    }
}
