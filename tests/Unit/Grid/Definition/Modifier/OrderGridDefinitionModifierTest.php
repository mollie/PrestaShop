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

namespace Grid\Definition\Modifier;

use Mollie;
use Mollie\Grid\Definition\Modifier\OrderGridDefinitionModifier;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

class OrderGridDefinitionModifierTest extends TestCase
{
    /**
     * Regression for PIPRES-802: the column header and the resend tooltip must be
     * resolved through the module's legacy translator (Mollie::l), which is backed by
     * the shipped translations/<locale>.php catalogs, so they follow the back-office
     * language. Before the fix they used the Symfony translator with the "Modules.mollie"
     * domain, which ships no catalog and therefore always fell back to English.
     */
    public function testItTranslatesColumnAndTooltipThroughTheModuleTranslator()
    {
        /** @var Mollie $module */
        $module = $this->createMock(Mollie::class);
        $module->method('l')->willReturnCallback(function ($string, $source) {
            // Both strings must be scoped to this modifier's own translation source.
            Assert::assertSame('OrderGridDefinitionModifier', $source);

            return 'translated:' . $string;
        });

        $columns = new ColumnCollection();
        $columns->add(new DataColumn('date_add'));

        $gridDefinition = $this->createMock(GridDefinitionInterface::class);
        $gridDefinition->method('getColumns')->willReturn($columns);

        (new OrderGridDefinitionModifier($module))->modify($gridDefinition);

        $secondChance = null;
        /** @var ColumnInterface $column */
        foreach ($columns as $column) {
            if ('second_chance' === $column->getId()) {
                $secondChance = $column;
                break;
            }
        }

        $this->assertNotNull($secondChance, 'The second_chance column should be added to the grid.');
        $this->assertSame('translated:Resend payment link', $secondChance->getName());

        $options = $secondChance->getOptions();
        $this->assertArrayHasKey('actions', $options);

        $resendAction = null;
        /** @var RowActionInterface $action */
        foreach ($options['actions'] as $action) {
            $resendAction = $action;
            break;
        }

        $this->assertNotNull($resendAction, 'The resend row action should be present.');
        $this->assertSame(
            'translated:You will resend email with payment link to the customer',
            $resendAction->getName()
        );
    }
}
