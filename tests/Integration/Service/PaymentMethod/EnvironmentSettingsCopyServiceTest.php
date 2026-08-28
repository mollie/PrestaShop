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

namespace Mollie\Tests\Integration\Service\PaymentMethod;

use Module;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\CustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiKeyService;
use Mollie\Service\PaymentMethod\EnvironmentSettingsCopyService;
use Mollie\Tests\Integration\BaseTestCase;
use MolPaymentMethod;

class EnvironmentSettingsCopyServiceTest extends BaseTestCase
{
    /** @var int */
    private $shopId;

    /** @var CustomerRepository */
    private $customerRepository;

    /** @var CountryRepository */
    private $countryRepository;

    public function setUp(): void
    {
        parent::setUp();

        foreach (['mol_payment_method', 'mol_excluded_customer_groups', 'mol_country', 'mol_excluded_country'] as $table) {
            $this->truncateTable($table);
        }

        $this->shopId = (int) \Context::getContext()->shop->id;
        $this->customerRepository = $this->getService(CustomerRepository::class);
        $this->countryRepository = $this->getService(CountryRepository::class);

        $this->configuration->updateValue(Config::MOLLIE_API_KEY, 'live_dummykey', $this->shopId);
    }

    public function tearDown(): void
    {
        foreach (['mol_payment_method', 'mol_excluded_customer_groups', 'mol_country', 'mol_excluded_country'] as $table) {
            $this->truncateTable($table);
        }

        \Configuration::deleteByName(Config::MOLLIE_API_KEY);
        \Configuration::deleteByName(Config::MOLLIE_IFRAME['sandbox']);
        \Configuration::deleteByName(Config::MOLLIE_IFRAME['production']);
        \Configuration::deleteByName(Config::MOLLIE_SINGLE_CLICK_PAYMENT['sandbox']);
        \Configuration::deleteByName(Config::MOLLIE_SINGLE_CLICK_PAYMENT['production']);

        parent::tearDown();
    }

    public function testCopyMirrorsTestConfigurationOntoLiveAndIsIdempotent(): void
    {
        $idealTestId = $this->seedPaymentMethod('ideal', Config::ENVIRONMENT_TEST, $this->shopId, 2, [
            'enabled' => true,
            'surcharge' => 2,
            'surcharge_fixed_amount_tax_excl' => 1.99,
            'min_amount' => 10.0,
            'max_amount' => 100.0,
        ]);
        $this->seedPaymentMethod('creditcard', Config::ENVIRONMENT_TEST, $this->shopId, 1, [
            'enabled' => true,
            'is_manual_capture' => true,
        ]);
        $this->seedPaymentMethod('giftcard', Config::ENVIRONMENT_TEST, $this->shopId, 3, ['enabled' => true]);
        $this->seedPaymentMethod('unsupportedmethod', Config::ENVIRONMENT_TEST, $this->shopId, 4, ['enabled' => true]);

        $bancontactLiveId = $this->seedPaymentMethod('bancontact', Config::ENVIRONMENT_LIVE, $this->shopId, 1, ['enabled' => true]);
        $siblingShopTestId = $this->seedPaymentMethod('ideal', Config::ENVIRONMENT_TEST, $this->shopId + 1, 1, ['enabled' => true]);

        $this->customerRepository->updatePaymentMethodExcludedCustomerGroups($idealTestId, [4]);
        $this->countryRepository->updatePaymentMethodCountries($idealTestId, [8, 17]);
        $this->countryRepository->updatePaymentMethodExcludedCountries($idealTestId, [26]);

        $service = $this->buildService(['ideal', 'creditcard', 'bancontact']);

        $firstRun = $service->copy($this->shopId);
        $secondRun = $service->copy($this->shopId);

        $this->assertFalse($firstRun['liveKeyMissing']);
        $this->assertSame(['creditcard', 'ideal'], $firstRun['copied']);
        $this->assertSame(['giftcard'], $firstRun['skipped']);
        $this->assertSame($firstRun['copied'], $secondRun['copied']);

        $liveRows = $this->getPaymentMethodRows(Config::ENVIRONMENT_LIVE, $this->shopId);
        $this->assertSame(['creditcard', 'ideal', 'bancontact'], array_column($liveRows, 'id_method'));
        $this->assertSame(['1', '2', '3'], array_column($liveRows, 'position'));

        $idealLiveRow = $liveRows[1];
        $this->assertSame('1', $idealLiveRow['enabled']);
        $this->assertSame('2', $idealLiveRow['surcharge']);
        $this->assertSame(1.99, (float) $idealLiveRow['surcharge_fixed_amount_tax_excl']);
        $this->assertSame(10.0, (float) $idealLiveRow['min_amount']);
        $this->assertSame(100.0, (float) $idealLiveRow['max_amount']);
        $this->assertNotSame((int) $idealLiveRow['id_payment_method'], $idealTestId);

        $creditcardLiveRow = $liveRows[0];
        $this->assertSame('1', $creditcardLiveRow['is_manual_capture']);

        $idealLiveId = (int) $idealLiveRow['id_payment_method'];
        $this->assertSame(['4'], $this->customerRepository->getExcludedCustomerGroupIds($idealLiveId));
        $this->assertSame(['8', '17'], $this->countryRepository->getMethodCountryIds($idealLiveId));
        $this->assertSame(['26'], $this->countryRepository->getExcludedCountryIds($idealLiveId));

        $this->assertSame(['4'], $this->customerRepository->getExcludedCustomerGroupIds($idealTestId));
        $this->assertCount(4, $this->getPaymentMethodRows(Config::ENVIRONMENT_TEST, $this->shopId));
        $this->assertCount(1, $this->getPaymentMethodRows(Config::ENVIRONMENT_TEST, $this->shopId + 1));

        $bancontactRow = new MolPaymentMethod($bancontactLiveId);
        $this->assertSame(3, (int) $bancontactRow->position);

        $siblingRow = new MolPaymentMethod($siblingShopTestId);
        $this->assertSame($this->shopId + 1, (int) $siblingRow->id_shop);
    }

    public function testCopyTransfersEnvironmentPairedConfigs(): void
    {
        $this->configuration->updateValue(Config::MOLLIE_IFRAME['sandbox'], 1, $this->shopId);
        $this->configuration->updateValue(Config::MOLLIE_IFRAME['production'], 0, $this->shopId);
        $this->configuration->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT['sandbox'], 0, $this->shopId);
        $this->configuration->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT['production'], 1, $this->shopId);

        $this->buildService([])->copy($this->shopId);

        $this->assertSame(1, (int) $this->configuration->get(Config::MOLLIE_IFRAME['production'], $this->shopId));
        $this->assertSame(0, (int) $this->configuration->get(Config::MOLLIE_SINGLE_CLICK_PAYMENT['production'], $this->shopId));
    }

    public function testCopyReportsLiveKeyMissingWhenNoLiveKeySaved(): void
    {
        \Configuration::deleteByName(Config::MOLLIE_API_KEY);
        $this->seedPaymentMethod('ideal', Config::ENVIRONMENT_TEST, $this->shopId, 1, ['enabled' => true]);

        $result = $this->buildService(['ideal'])->copy($this->shopId);

        $this->assertTrue($result['liveKeyMissing']);
        $this->assertSame([], $result['copied']);
        $this->assertCount(0, $this->getPaymentMethodRows(Config::ENVIRONMENT_LIVE, $this->shopId));
    }

    private function buildService(array $liveActivatedMethodIds): EnvironmentSettingsCopyService
    {
        $collection = new MethodCollection(count($liveActivatedMethodIds), null);

        foreach ($liveActivatedMethodIds as $methodId) {
            $apiMethod = new \stdClass();
            $apiMethod->id = $methodId;
            $apiMethod->status = 'activated';
            $collection->append($apiMethod);
        }

        $methodEndpoint = $this->getMockBuilder(MethodEndpoint::class)
            ->disableOriginalConstructor()
            ->getMock();
        $methodEndpoint->method('allAvailable')->willReturn($collection);

        $client = $this->getMockBuilder(MollieApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->methods = $methodEndpoint;

        $apiKeyService = $this->createMock(ApiKeyService::class);
        $apiKeyService->method('setApiKey')->willReturn($client);

        return new EnvironmentSettingsCopyService(
            $this->getService(PaymentMethodRepositoryInterface::class),
            $this->customerRepository,
            $this->countryRepository,
            $apiKeyService,
            $this->getService(ConfigurationAdapter::class),
            Module::getInstanceByName('mollie'),
            $this->getService(LoggerInterface::class)
        );
    }

    private function seedPaymentMethod(string $methodId, int $environment, int $shopId, int $position, array $overrides = []): int
    {
        $paymentMethod = new MolPaymentMethod();
        $paymentMethod->id_method = $methodId;
        $paymentMethod->method_name = $methodId;
        $paymentMethod->method = 'payments';
        $paymentMethod->description = '';
        $paymentMethod->enabled = false;
        $paymentMethod->is_countries_applicable = false;
        $paymentMethod->minimal_order_value = '0';
        $paymentMethod->max_order_value = '0';
        $paymentMethod->surcharge = 0;
        $paymentMethod->surcharge_fixed_amount_tax_excl = 0;
        $paymentMethod->tax_rules_group_id = 0;
        $paymentMethod->surcharge_percentage = 0;
        $paymentMethod->surcharge_limit = 0;
        $paymentMethod->images_json = '';
        $paymentMethod->min_amount = 0;
        $paymentMethod->max_amount = 0;
        $paymentMethod->live_environment = (bool) $environment;
        $paymentMethod->position = $position;
        $paymentMethod->id_shop = $shopId;
        $paymentMethod->is_manual_capture = false;

        foreach ($overrides as $field => $value) {
            $paymentMethod->{$field} = $value;
        }

        $paymentMethod->save();

        return (int) $paymentMethod->id;
    }

    private function getPaymentMethodRows(int $environment, int $shopId): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'mol_payment_method`
            WHERE live_environment = ' . (int) $environment . ' AND id_shop = ' . (int) $shopId . '
            ORDER BY position ASC'
        );

        return $rows ?: [];
    }
}
