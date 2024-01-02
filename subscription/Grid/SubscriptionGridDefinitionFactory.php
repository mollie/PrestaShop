<?php

declare(strict_types=1);

namespace Mollie\Subscription\Grid;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use PrestaShopBundle\Form\Admin\Type\DateRangeType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SubscriptionGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    private const FILE_NAME = 'SubscriptionGridDefinitionFactory';

    public const GRID_ID = 'invertus_mollie_subscription';

    /** @var \Mollie */
    private $module;
    /** @var AccessibilityCheckerInterface */
    private $subscriptionCancelAccessibilityChecker;

    public function __construct(
        HookDispatcherInterface $hookDispatcher = null,
        \Mollie $module,
        AccessibilityCheckerInterface $subscriptionCancelAccessibilityChecker
    ) {
        parent::__construct($hookDispatcher);
        $this->module = $module;
        $this->subscriptionCancelAccessibilityChecker = $subscriptionCancelAccessibilityChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function getId()
    {
        return self::GRID_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return $this->module->l('Subscriptions', self::FILE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_mol_recurring_order'))
                ->setName($this->module->l('ID', self::FILE_NAME))
                ->setOptions([
                    'field' => 'id_mol_recurring_order',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('mollie_subscription_id'))
                ->setName($this->module->l('Subscription ID', self::FILE_NAME))
                ->setOptions([
                    'field' => 'mollie_subscription_id',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('mollie_customer_id'))
                ->setName($this->module->l('Customer ID', self::FILE_NAME))
                ->setOptions([
                    'field' => 'mollie_customer_id',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('fullname'))
                ->setName($this->module->l('Full name', self::FILE_NAME))
                ->setOptions([
                    'field' => 'fullname',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('description'))
                ->setName($this->module->l('Description', self::FILE_NAME))
                ->setOptions([
                    'field' => 'description',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('status'))
                ->setName($this->module->l('Status', self::FILE_NAME))
                ->setOptions([
                    'field' => 'status',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('total_price'))
                ->setName($this->module->l('Total price', self::FILE_NAME))
                ->setOptions([
                    'field' => 'total_price',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('iso_code'))
                ->setName($this->module->l('Currency', self::FILE_NAME))
                ->setOptions([
                    'field' => 'iso_code',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('recurring_order.date_add'))
                ->setName($this->module->l('Created at', self::FILE_NAME))
                ->setOptions([
                    'field' => 'date_add',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('recurring_order.date_update'))
                ->setName($this->module->l('Updated at', self::FILE_NAME))
                ->setOptions([
                    'field' => 'date_update',
                    'sortable' => true,
                ])
            )
            ->add((new DataColumn('recurring_order.cancelled_at'))
                ->setName($this->module->l('Canceled at', self::FILE_NAME))
                ->setOptions([
                    'field' => 'cancelled_at',
                    'sortable' => true,
                ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->module->l('Actions', self::FILE_NAME))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new SubmitRowAction('cancel'))
                                    ->setName($this->module->l('Cancel', self::FILE_NAME))
                                    ->setIcon('delete')
                                    ->setOptions([
                                        'route' => 'admin_subscription_cancel',
                                        'route_param_name' => 'subscriptionId',
                                        'route_param_field' => 'id_mol_recurring_order',
                                        'confirm_message' => $this->module->l('Cancel selected subscription?', self::FILE_NAME),
                                        'accessibility_checker' => $this->subscriptionCancelAccessibilityChecker,
                                    ])
                            ),
                    ])
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters()
    {
        return (new FilterCollection())
            ->add((new Filter('id_mol_recurring_order', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('ID', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('id_mol_recurring_order')
            )
            ->add((new Filter('mollie_subscription_id', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Subscription ID', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('mollie_subscription_id')
            )
            ->add((new Filter('mollie_customer_id', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Customer ID', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('mollie_customer_id')
            )
            ->add((new Filter('fullname', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Full name', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('fullname')
            )
            ->add((new Filter('description', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Description', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('description')
            )
            ->add((new Filter('status', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Status', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('status')
            )
            ->add((new Filter('total_price', MoneyType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Total price', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('total_price')
            )
            ->add((new Filter('iso_code', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                    'attr' => [
                        'placeholder' => $this->module->l('Currency', self::FILE_NAME),
                    ],
                ])
                ->setAssociatedColumn('iso_code')
            )
            ->add((new Filter('date_add', DateRangeType::class))
                ->setAssociatedColumn('recurring_order.date_add')
            )
            ->add((new Filter('date_update', DateRangeType::class))
                ->setAssociatedColumn('recurring_order.date_update')
            )
            ->add((new Filter('cancelled_at', DateRangeType::class))
                ->setAssociatedColumn('recurring_order.cancelled_at')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'admin_subscription_index',
                    ])
                    ->setAssociatedColumn('actions')
            );
    }
}
