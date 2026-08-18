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
use Mollie\Grid\Action\Type\ViewInMollieRowAction;
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
    const VIEW_IN_MOLLIE = 'View in Mollie';

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
     * Regression for PIPRES-810: the "View in Mollie" label (PIPRES-786) shipped in English only, so
     * every non-English back office showed the source string on the Orders list and the order page.
     * The label is looked up from two sources - the order_info template and this grid modifier - so
     * both keys must exist, and must agree, in every shipped catalog.
     */
    public function testShippedCatalogsTranslateTheViewInMollieLabel()
    {
        $hash = md5(self::VIEW_IN_MOLLIE);
        $templateKey = '<{mollie}prestashop>order_info_' . $hash;
        $gridKey = '<{mollie}prestashop>' . strtolower(self::TRANSLATION_SOURCE) . '_' . $hash;
        $translationsDir = dirname(__DIR__, 5) . '/translations';

        $catalogs = glob($translationsDir . '/*.php');
        $this->assertNotEmpty($catalogs, 'No shipped translation catalogs were found.');

        $checked = 0;
        foreach ($catalogs as $catalog) {
            $iso = basename($catalog, '.php');
            if ('index' === $iso) {
                continue;
            }

            ++$checked;
            $_MODULE = [];
            include $catalog;

            $this->assertArrayHasKey($templateKey, $_MODULE, sprintf('Missing order page translation in %s.php', $iso));
            $this->assertArrayHasKey($gridKey, $_MODULE, sprintf('Missing Orders list translation in %s.php', $iso));
            $this->assertNotSame('', trim((string) $_MODULE[$templateKey]), sprintf('Empty order page translation in %s.php', $iso));
            $this->assertSame(
                $_MODULE[$templateKey],
                $_MODULE[$gridKey],
                sprintf('The order page and Orders list labels must match in %s.php', $iso)
            );

            if ('en' !== $iso) {
                $this->assertNotSame(
                    self::VIEW_IN_MOLLIE,
                    $_MODULE[$gridKey],
                    sprintf('Label in %s.php should be localized, not the English source string', $iso)
                );
            }
        }

        // Guards against the glob silently matching nothing but index.php.
        $this->assertGreaterThan(1, $checked, 'Expected the shipped locale catalogs to be checked.');
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
            ViewInMollieRowAction::class,
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

        $capturedColumns = [];
        $columns = $this->createMock(ColumnCollectionInterface::class);
        $columns->method('addBefore')->willReturnCallback(function ($id, $column) use (&$capturedColumns) {
            Assert::assertSame('date_add', $id);
            $capturedColumns[$column->getId()] = $column;

            return null;
        });

        $gridDefinition = $this->createMock(GridDefinitionInterface::class);
        $gridDefinition->method('getColumns')->willReturn($columns);

        (new OrderGridDefinitionModifier($module))->modify($gridDefinition);

        $this->assertArrayHasKey('second_chance', $capturedColumns, 'The second_chance column should be added before date_add.');
        $secondChanceColumn = $capturedColumns['second_chance'];
        $this->assertSame('translated:' . self::HEADER, $secondChanceColumn->getName());

        $options = $secondChanceColumn->getOptions();
        $this->assertArrayHasKey('actions', $options);

        $resendAction = $this->firstAction($options['actions']);
        $this->assertNotNull($resendAction, 'The resend row action should be present.');
        $this->assertSame('translated:' . self::TOOLTIP, $resendAction->getName());

        $this->assertArrayHasKey('mollie_view_in_dashboard', $capturedColumns, 'The View in Mollie column should be added before date_add.');
        $viewInMollieColumn = $capturedColumns['mollie_view_in_dashboard'];
        $this->assertSame('translated:Mollie', $viewInMollieColumn->getName());

        $viewAction = $this->firstAction($viewInMollieColumn->getOptions()['actions']);
        $this->assertNotNull($viewAction, 'The View in Mollie row action should be present.');
        $this->assertSame('translated:View in Mollie', $viewAction->getName());
    }

    private function firstAction($actions)
    {
        foreach ($actions as $action) {
            return $action;
        }

        return null;
    }
}
