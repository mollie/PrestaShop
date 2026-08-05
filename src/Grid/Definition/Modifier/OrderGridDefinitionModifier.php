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

namespace Mollie\Grid\Definition\Modifier;

use Mollie;
use Mollie\Config\Config;
use Mollie\Grid\Action\Type\SecondChanceRowAction;
use Mollie\Grid\Action\Type\ViewInMollieRowAction;
use Mollie\Grid\Row\AccessibilityChecker\SecondChanceAccessibilityChecker;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderGridDefinitionModifier implements GridDefinitionModifierInterface
{
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * {@inheritDoc}
     */
    public function modify(GridDefinitionInterface $gridDefinition)
    {
        $gridDefinition->getColumns()
            ->addBefore('date_add', (new ActionColumn('second_chance'))
                ->setName($this->module->l('Resend payment link', 'OrderGridDefinitionModifier'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add((new SecondChanceRowAction('transaction_id'))
                            ->setName($this->module->l('You will resend email with payment link to the customer', 'OrderGridDefinitionModifier'))
                            ->setOptions([
                                'route' => Mollie\Config\Config::ROUTE_RESEND_SECOND_CHANCE_PAYMENT_MESSAGE,
                                'route_param_field' => 'id_order',
                                'route_param_name' => 'orderId',
                                'use_inline_display' => true,
                                'accessibility_checker' => $this->module->getService(
                                    SecondChanceAccessibilityChecker::class
                                ),
                            ])
                        ),
                ])
            );

        $gridDefinition->getColumns()
            ->addBefore('date_add', (new ActionColumn('mollie_view_in_dashboard'))
                ->setName($translator->trans('Mollie', [], 'Modules.mollie'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add((new ViewInMollieRowAction('view_in_mollie'))
                            ->setName($translator->trans('View in Mollie', [], 'Modules.mollie'))
                            ->setIcon('open_in_new')
                            ->setOptions([
                                'route' => Config::ROUTE_VIEW_IN_MOLLIE_DASHBOARD,
                                'route_param_field' => 'transaction_id',
                                'route_param_name' => 'transactionId',
                                'target' => '_blank',
                                'use_inline_display' => true,
                                'accessibility_checker' => static function (array $record) {
                                    return !empty($record['transaction_id']);
                                },
                            ])
                        ),
                ])
            );
    }
}
