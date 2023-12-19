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

namespace Mollie\Tests\Integration\Subscription\Provider;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Shared\Infrastructure\Exception\MollieDatabaseException;
use Mollie\Subscription\Provider\GeneralSubscriptionMailDataProvider;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\CurrencyFactory;
use Mollie\Tests\Integration\Factory\CustomerFactory;
use Mollie\Tests\Integration\Factory\MolRecurringOrderFactory;
use Mollie\Tests\Integration\Factory\MolRecurringOrdersProductFactory;
use Mollie\Tests\Integration\Factory\ProductFactory;
use Mollie\Utility\NumberUtility;

class GeneralSubscriptionMailDataProviderTest extends BaseTestCase
{
    public function testItSuccessfullyProvidesData(): void
    {
        $customer = CustomerFactory::create();

        /** @var \Currency $currency */
        $currency = CurrencyFactory::initialize()->create();

        /** @var \Product $product */
        $product = ProductFactory::initialize()->create();

        /** @var \MolRecurringOrdersProduct $recurringOrderProduct */
        $recurringOrderProduct = MolRecurringOrdersProductFactory::initialize()->create([
            'id_product' => $product->id,
        ]);

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = MolRecurringOrderFactory::initialize()->create([
            'id_customer' => $customer->id,
            'id_mol_recurring_orders_product' => $recurringOrderProduct->id,
            'id_currency' => $currency->id,
        ]);

        /** @var GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider */
        $generalSubscriptionMailDataProvider = $this->getService(GeneralSubscriptionMailDataProvider::class);

        $result = $generalSubscriptionMailDataProvider->run((int) $recurringOrder->id);

        $expectedProductUnitPriceTaxExcl = (string) NumberUtility::toPrecision(
            (float) $recurringOrderProduct->unit_price,
            NumberUtility::DECIMAL_PRECISION
        );

        $expectedTotalPriceTaxIncl = (string) NumberUtility::toPrecision(
            (float) $recurringOrder->total_tax_incl,
            NumberUtility::DECIMAL_PRECISION
        );

        $this->assertEquals((string) $recurringOrder->mollie_subscription_id, $result->getMollieSubscriptionId());
        $this->assertEquals((string) $product->name, $result->getProductName());
        $this->assertEquals($expectedProductUnitPriceTaxExcl, $result->getProductUnitPriceTaxExcl());
        $this->assertEquals((int) $recurringOrderProduct->quantity, $result->getProductQuantity());
        $this->assertEquals($expectedTotalPriceTaxIncl, $result->getTotalOrderPriceTaxIncl());
        $this->assertEquals((string) $customer->firstname, $result->getFirstName());
        $this->assertEquals((string) $customer->lastname, $result->getLastName());
        $this->assertEquals((int) $customer->id_lang, $result->getLangId());
        $this->assertEquals((int) $customer->id_shop, $result->getShopId());

        $this->assertEquals([
            'subscription_reference' => (string) $recurringOrder->mollie_subscription_id,
            'product_name' => (string) $product->name,
            'unit_price' => $expectedProductUnitPriceTaxExcl,
            'quantity' => (int) $recurringOrderProduct->quantity,
            'total_price' => $expectedTotalPriceTaxIncl,
            'firstName' => (string) $customer->firstname,
            'lastName' => (string) $customer->lastname,
        ], $result->toArray());
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindRecurringOrder(): void
    {
        /** @var GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider */
        $generalSubscriptionMailDataProvider = $this->getService(GeneralSubscriptionMailDataProvider::class);

        $this->expectException(MollieDatabaseException::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_FAILED_TO_FIND_RECORD);
        $this->expectExceptionMessageRegExp('/' . \MolRecurringOrder::class . '/');

        $generalSubscriptionMailDataProvider->run(0);
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindRecurringOrderProduct(): void
    {
        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = MolRecurringOrderFactory::initialize()->create([
            'id_mol_recurring_orders_product' => 0,
        ]);

        /** @var GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider */
        $generalSubscriptionMailDataProvider = $this->getService(GeneralSubscriptionMailDataProvider::class);

        $this->expectException(MollieDatabaseException::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_FAILED_TO_FIND_RECORD);
        $this->expectExceptionMessageRegExp('/' . \MolRecurringOrdersProduct::class . '/');

        $generalSubscriptionMailDataProvider->run((int) $recurringOrder->id);
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindCustomer(): void
    {
        /** @var \MolRecurringOrdersProduct $recurringOrderProduct */
        $recurringOrderProduct = MolRecurringOrdersProductFactory::initialize()->create();

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = MolRecurringOrderFactory::initialize()->create([
            'id_mol_recurring_orders_product' => $recurringOrderProduct->id,
            'id_customer' => 0,
        ]);

        /** @var GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider */
        $generalSubscriptionMailDataProvider = $this->getService(GeneralSubscriptionMailDataProvider::class);

        $this->expectException(MollieDatabaseException::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_FAILED_TO_FIND_RECORD);
        $this->expectExceptionMessageRegExp('/' . \Customer::class . '/');

        $generalSubscriptionMailDataProvider->run((int) $recurringOrder->id);
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindProduct(): void
    {
        /** @var \MolRecurringOrdersProduct $recurringOrderProduct */
        $recurringOrderProduct = MolRecurringOrdersProductFactory::initialize()->create([
            'id_product' => 0,
        ]);

        $customer = CustomerFactory::create();

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = MolRecurringOrderFactory::initialize()->create([
            'id_mol_recurring_orders_product' => $recurringOrderProduct->id,
            'id_customer' => $customer->id,
        ]);

        /** @var GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider */
        $generalSubscriptionMailDataProvider = $this->getService(GeneralSubscriptionMailDataProvider::class);

        $this->expectException(MollieDatabaseException::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_FAILED_TO_FIND_RECORD);
        $this->expectExceptionMessageRegExp('/' . \Product::class . '/');

        $generalSubscriptionMailDataProvider->run((int) $recurringOrder->id);
    }

    public function testItUnsuccessfullyProvidesDataFailedToFindCurrency(): void
    {
        /** @var \Product $product */
        $product = ProductFactory::initialize()->create();

        /** @var \MolRecurringOrdersProduct $recurringOrderProduct */
        $recurringOrderProduct = MolRecurringOrdersProductFactory::initialize()->create([
            'id_product' => $product->id,
        ]);

        $customer = CustomerFactory::create();

        /** @var \MolRecurringOrder $recurringOrder */
        $recurringOrder = MolRecurringOrderFactory::initialize()->create([
            'id_mol_recurring_orders_product' => $recurringOrderProduct->id,
            'id_customer' => $customer->id,
            'id_currency' => 0,
        ]);

        /** @var GeneralSubscriptionMailDataProvider $generalSubscriptionMailDataProvider */
        $generalSubscriptionMailDataProvider = $this->getService(GeneralSubscriptionMailDataProvider::class);

        $this->expectException(MollieDatabaseException::class);
        $this->expectExceptionCode(ExceptionCode::INFRASTRUCTURE_FAILED_TO_FIND_RECORD);
        $this->expectExceptionMessageRegExp('/' . \Currency::class . '/');

        $generalSubscriptionMailDataProvider->run((int) $recurringOrder->id);
    }
}
