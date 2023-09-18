<?php

declare(strict_types=1);

namespace Mollie\Subscription\Grid;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Mollie\Subscription\Config\Config;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Provides sql for subscription list
 */
class SubscriptionGridQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $searchCriteriaApplicator;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
     */
    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    /**
     * Get query that searches grid rows.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters())
            ->select('recurring_order.*')
            ->addSelect($this->getNameField() . ' as fullname')
            ->addSelect('recurring_order.total_tax_incl as total_price')
            ->addSelect('currency.iso_code')
        ;

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb);

        return $qb;
    }

    /**
     * Get query that counts grid rows.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters());
        $qb->select('COUNT(recurring_order.id_mol_recurring_order)');

        return $qb;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return QueryBuilder
     */
    private function getQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->from($this->dbPrefix . Config::DB_PREFIX . 'recurring_order', 'recurring_order');

        $qb->leftJoin('recurring_order', $this->dbPrefix . Config::DB_PREFIX . 'recurring_orders_product', 'recurring_orders_product', 'recurring_order.id_mol_recurring_orders_product = recurring_orders_product.id_mol_recurring_orders_product');
        $qb->leftJoin('recurring_order', $this->dbPrefix . 'orders', 'orders', 'recurring_order.id_order = orders.id_order');
        $qb->leftJoin('orders', $this->dbPrefix . 'customer', 'customer', 'orders.id_customer = customer.id_customer');
        $qb->leftJoin('recurring_orders_product', $this->dbPrefix . 'currency', 'currency', 'currency.id_currency = recurring_order.id_currency');

        $this->applyFilters($filters, $qb);

        return $qb;
    }

    /**
     * @param array<string, mixed> $filters
     * @param QueryBuilder $qb
     */
    private function applyFilters(array $filters, QueryBuilder $qb): void
    {
        $likeComparisonFilters = [
            'id_mol_recurring_order' => 'id_mol_recurring_order',
            'mollie_subscription_id' => 'mollie_subscription_id',
            'mollie_customer_id' => 'mollie_customer_id',
            'fullname' => $this->getNameField(),
            'description' => 'recurring_order.description',
            'status' => 'recurring_order.status',
            'total_price' => 'recurring_order.total_tax_incl',
            'iso_code' => 'currency.iso_code',
        ];

        $dateComparisonFilters = [
            'date_add' => 'recurring_order.date_add',
            'date_update' => 'recurring_order.date_update',
            'cancelled_at' => 'recurring_order.cancelled_at',
        ];

        foreach ($filters as $filterName => $value) {
            if ('fullname' === $filterName) {
                $qb->andWhere($likeComparisonFilters[$filterName] . ' LIKE :' . $filterName)
                    ->setParameter($filterName, '%' . $value . '%');
                continue;
            }

            if (array_key_exists($filterName, $likeComparisonFilters)) {
                $qb->andWhere($likeComparisonFilters[$filterName] . ' LIKE :' . $filterName)
                    ->setParameter($filterName, '%' . $value . '%');
                continue;
            }

            if (array_key_exists($filterName, $dateComparisonFilters)) {
                $alias = $dateComparisonFilters[$filterName];

                foreach ($value as $name => $dateValue) {
                    switch ($name) {
                        case 'from':
                            $qb->andWhere("$alias >= :$name");
                            $qb->setParameter($name, sprintf('%s %s', $dateValue, '0:0:0'));
                            break;
                        case 'to':
                            $qb->andWhere("$alias <= :$name");
                            $qb->setParameter($name, sprintf('%s %s', $dateValue, '23:59:59'));
                            break;
                    }
                }
            }
        }
    }

    private function getNameField(): string
    {
        return 'CONCAT(customer.firstname, " ", customer.lastname)';
    }
}
