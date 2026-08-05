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
use Mollie\Grid\Action\Type\SecondChanceRowAction;
use Mollie\Grid\Definition\Modifier\OrderGridDefinitionModifier;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

/**
 * Regression for PIPRES-802: the order-list "Resend payment link" column header and the
 * resend-action tooltip always rendered in English. The grid modifier translated them with the
 * Symfony translator under the "Modules.mollie" domain, for which the module ships no catalog, so
 * every language fell back to the English source string. The fix routes both strings through the
 * module's legacy translator (Mollie::l), which is backed by the shipped translations/<locale>.php
 * catalogs, and adds the two missing keys to every shipped locale.
 */
class OrderGridDefinitionModifierTest extends TestCase
{
    const TRANSLATION_SOURCE = 'OrderGridDefinitionModifier';
    const HEADER = 'Resend payment link';
    const TOOLTIP = 'You will resend email with payment link to the customer';

    /**
     * The shipped catalogs must contain the exact keys that Mollie::l() looks up for this modifier,
     * so the header and tooltip follow the back-office language instead of falling back to English.
     * Pure PHP, no PrestaShop dependency, so it runs on every supported version.
     */
    public function testShippedCatalogsTranslateTheResendColumnStrings()
    {
        $prefix = '<{mollie}prestashop>' . strtolower(self::TRANSLATION_SOURCE) . '_';
        $headerKey = $prefix . md5(self::HEADER);
        $tooltipKey = $prefix . md5(self::TOOLTIP);
        $translationsDir = dirname(__DIR__, 5) . '/translations';

        foreach (['en', 'nl', 'fr'] as $iso) {
            $_MODULE = [];
            include $translationsDir . '/' . $iso . '.php';

            $this->assertArrayHasKey($headerKey, $_MODULE, sprintf('Missing header translation in %s.php', $iso));
            $this->assertArrayHasKey($tooltipKey, $_MODULE, sprintf('Missing tooltip translation in %s.php', $iso));
            $this->assertNotSame('', trim((string) $_MODULE[$headerKey]));

            if ('en' !== $iso) {
                $this->assertNotSame(
                    self::HEADER,
                    $_MODULE[$headerKey],
                    sprintf('Header in %s.php should be localized, not the English source string', $iso)
                );
            }
        }
    }

    /**
     * The modifier must build the column header and the resend tooltip via the module's legacy
     * translator (Mollie::l) with this modifier's own source, not the Symfony translator. Skips when
     * the PrestaShop grid classes the modifier instantiates are not autoloadable in the running test
     * environment, so it can only run-and-assert or skip - never error on a missing core class.
     */
    public function testItTranslatesColumnAndTooltipThroughTheModuleTranslator()
    {
        foreach ([
            ColumnCollectionInterface::class,
            GridDefinitionInterface::class,
            ActionColumn::class,
            RowActionCollection::class,
            SecondChanceRowAction::class,
        ] as $class) {
            if (!class_exists($class) && !interface_exists($class)) {
                $this->markTestSkipped(sprintf('PrestaShop grid dependency %s is not available here.', $class));
            }
        }

        /** @var Mollie $module */
        $module = $this->createMock(Mollie::class);
        $module->method('l')->willReturnCallback(function ($string, $source) {
            // Both strings must be scoped to this modifier's own translation source.
            Assert::assertSame(self::TRANSLATION_SOURCE, $source);

            return 'translated:' . $string;
        });

        $capturedColumn = null;
        $columns = $this->createMock(ColumnCollectionInterface::class);
        $columns->method('addBefore')->willReturnCallback(function ($id, $column) use (&$capturedColumn) {
            Assert::assertSame('date_add', $id);
            $capturedColumn = $column;

            return null;
        });

        $gridDefinition = $this->createMock(GridDefinitionInterface::class);
        $gridDefinition->method('getColumns')->willReturn($columns);

        (new OrderGridDefinitionModifier($module))->modify($gridDefinition);

        $this->assertNotNull($capturedColumn, 'The second_chance column should be added before date_add.');
        $this->assertSame('translated:' . self::HEADER, $capturedColumn->getName());

        $options = $capturedColumn->getOptions();
        $this->assertArrayHasKey('actions', $options);

        $resendAction = null;
        foreach ($options['actions'] as $action) {
            $resendAction = $action;
            break;
        }

        $this->assertNotNull($resendAction, 'The resend row action should be present.');
        $this->assertSame('translated:' . self::TOOLTIP, $resendAction->getName());
    }
}
