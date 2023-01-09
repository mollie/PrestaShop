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
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters())
            ->select('recurring_order.*')
            ->addSelect('CONCAT(customer.firstname, " ", customer.lastname) as fullname');

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb);

        return $qb;
    }

    /**
     * Get query that counts grid rows.
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters());
        $qb->select('COUNT(recurring_order.id_mol_sub_recurring_order)');

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
            ->from($this->dbPrefix . Config::DB_PREFIX . 'recurring_order', 'recurring_order')
        ;

        $qb->leftJoin('recurring_order', $this->dbPrefix . 'orders', 'orders', 'recurring_order.id_order = orders.id_order');
        $qb->leftJoin('orders', $this->dbPrefix . 'customer', 'customer', 'orders.id_customer = customer.id_customer');

        $this->applyFilters($filters, $qb);

        return $qb;
    }

    /**
     * @param array<string, mixed> $filters
     * @param QueryBuilder $qb
     */
    private function applyFilters(array $filters, QueryBuilder $qb): void
    {
        $allowedFiltersMap = [
            'id_attribute_subgroup' => 'asg.id_attribute_subgroup',
            'name' => 'asgl.name',
            'description' => 'asgl.description',
            'attribute_group_name' => 'agl.name',
        ];

        foreach ($filters as $filterName => $value) {
            if (!array_key_exists($filterName, $allowedFiltersMap)) {
                continue;
            }

            if ('id_attribute_subgroup' === $filterName) {
                $qb->andWhere($allowedFiltersMap[$filterName] . ' = :' . $filterName)
                    ->setParameter($filterName, $value)
                ;
                continue;
            }

            $qb->andWhere($allowedFiltersMap[$filterName] . ' LIKE :' . $filterName)
                ->setParameter($filterName, '%' . $value . '%')
            ;
        }
    }
}
