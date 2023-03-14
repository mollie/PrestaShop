<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use ObjectModel;
use PrestaShopCollection;
use PrestaShopException;

abstract class AbstractRepository
{
    /**
     * @var string
     */
    private $fullyClassifiedClassName;

    /**
     * @param string|\stdClass $fullyClassifiedClassName
     */
    public function __construct($fullyClassifiedClassName)
    {
        if (is_object($fullyClassifiedClassName)) {
            $this->fullyClassifiedClassName = get_class($fullyClassifiedClassName);

            return;
        }
        $this->fullyClassifiedClassName = $fullyClassifiedClassName;
    }

    /**
     * @return PrestaShopCollection
     *
     * @throws PrestaShopException
     */
    public function findAll()
    {
        return new PrestaShopCollection($this->fullyClassifiedClassName);
    }

    /**
     * @param array $keyValueCriteria
     *
     * @return ObjectModel|null
     *
     * @throws PrestaShopException
     */
    public function findOneBy(array $keyValueCriteria)
    {
        $psCollection = new PrestaShopCollection($this->fullyClassifiedClassName);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $first = $psCollection->getFirst();

        /* @phpstan-ignore-next-line */
        return false === $first ? null : $first;
    }

    /**
     * @param array $keyValueCriteria
     *
     * @return PrestaShopCollection|null
     *
     * @throws PrestaShopException
     */
    public function findAllBy(array $keyValueCriteria)
    {
        $psCollection = new PrestaShopCollection($this->fullyClassifiedClassName);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $all = $psCollection->getAll();

        /* @phpstan-ignore-next-line */
        return false === $all ? null : $all;
    }
}
