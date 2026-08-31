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

declare(strict_types=1);

namespace Mollie\Service\PaymentMethod;

use Db;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\CountryRepositoryInterface;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiKeyService;
use Mollie\Utility\ExceptionUtility;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EnvironmentSettingsCopyService
{
    const FILE_NAME = 'EnvironmentSettingsCopyService';

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CountryRepositoryInterface */
    private $countryRepository;

    /** @var ApiKeyService */
    private $apiKeyService;

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var Mollie */
    private $module;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        CustomerRepositoryInterface $customerRepository,
        CountryRepositoryInterface $countryRepository,
        ApiKeyService $apiKeyService,
        ConfigurationAdapter $configuration,
        Mollie $module,
        LoggerInterface $logger
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->customerRepository = $customerRepository;
        $this->countryRepository = $countryRepository;
        $this->apiKeyService = $apiKeyService;
        $this->configuration = $configuration;
        $this->module = $module;
        $this->logger = $logger;
    }

    /**
     * @return array{liveKeyMissing: bool, copied: string[], skipped: string[]}
     *
     * @throws \Exception
     */
    public function copy(int $shopId): array
    {
        $liveMethodIds = $this->getLiveActivatedMethodIds($shopId);

        if ($liveMethodIds === null) {
            return ['liveKeyMissing' => true, 'copied' => [], 'skipped' => []];
        }

        $plan = $this->buildCopyPlan($shopId, $liveMethodIds);

        $db = Db::getInstance();
        $db->execute('START TRANSACTION');

        try {
            $copiedMethodIds = [];

            foreach ($plan['copyable'] as $testRow) {
                $this->copyMethodRow($testRow, $shopId);
                $copiedMethodIds[] = $testRow['id_method'];
            }

            $this->mirrorTestOrder($shopId, $copiedMethodIds);

            if ($copiedMethodIds) {
                $this->copyEnvironmentPairedConfigs($shopId);
            }

            $db->execute('COMMIT');
        } catch (\Exception $e) {
            $db->execute('ROLLBACK');

            $this->logger->error(sprintf('%s - Failed to copy test settings to live', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            throw $e;
        }

        $this->logger->info(sprintf('%s - Copied test settings to live', self::FILE_NAME), [
            'copied_count' => count($copiedMethodIds),
            'copied' => $copiedMethodIds,
            'skipped_count' => count($plan['skipped']),
            'skipped' => $plan['skipped'],
        ]);

        return [
            'liveKeyMissing' => false,
            'copied' => $copiedMethodIds,
            'skipped' => $plan['skipped'],
        ];
    }

    /**
     * @param string[] $liveMethodIds
     *
     * @return array{copyable: array<array<string, mixed>>, skipped: string[]}
     */
    private function buildCopyPlan(int $shopId, array $liveMethodIds): array
    {
        $testRows = $this->paymentMethodRepository->getMethodsForCheckout(Config::ENVIRONMENT_TEST, $shopId) ?: [];

        $copyable = [];
        $skipped = [];

        foreach ($testRows as $testRow) {
            if (!Config::isMethodSupported($testRow['id_method'])) {
                continue;
            }

            if (!in_array($testRow['id_method'], $liveMethodIds, true)) {
                $skipped[] = $testRow['id_method'];
                continue;
            }

            $copyable[] = $testRow;
        }

        usort($copyable, static function (array $a, array $b): int {
            return (int) $a['position'] <=> (int) $b['position'];
        });

        return ['copyable' => $copyable, 'skipped' => $skipped];
    }

    /**
     * @param array<string, mixed> $testRow
     */
    private function copyMethodRow(array $testRow, int $shopId): void
    {
        $liveId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId(
            $testRow['id_method'],
            Config::ENVIRONMENT_LIVE,
            $shopId
        );

        $paymentMethod = $liveId ? new MolPaymentMethod((int) $liveId) : new MolPaymentMethod();

        $paymentMethod->id_method = $testRow['id_method'];
        $paymentMethod->method_name = $testRow['method_name'];
        $paymentMethod->enabled = (bool) $testRow['enabled'];
        $paymentMethod->method = $testRow['method'];
        $paymentMethod->description = (string) $testRow['description'];
        $paymentMethod->is_countries_applicable = (bool) $testRow['is_countries_applicable'];
        $paymentMethod->surcharge = (int) $testRow['surcharge'];
        $paymentMethod->surcharge_fixed_amount_tax_excl = (float) $testRow['surcharge_fixed_amount_tax_excl'];
        $paymentMethod->tax_rules_group_id = (int) $testRow['tax_rules_group_id'];
        $paymentMethod->surcharge_percentage = (float) $testRow['surcharge_percentage'];
        $paymentMethod->surcharge_limit = (float) $testRow['surcharge_limit'];
        $paymentMethod->images_json = (string) $testRow['images_json'];
        $paymentMethod->min_amount = (float) $testRow['min_amount'];
        $paymentMethod->max_amount = (float) $testRow['max_amount'];
        $paymentMethod->is_manual_capture = (bool) $testRow['is_manual_capture'];
        $paymentMethod->live_environment = true;
        $paymentMethod->id_shop = $shopId;

        if (!$liveId) {
            $paymentMethod->position = $this->paymentMethodRepository->getMaxPosition(Config::ENVIRONMENT_LIVE, $shopId) + 1;
        }

        $paymentMethod->save();

        $testPaymentMethodId = (int) $testRow['id_payment_method'];
        $livePaymentMethodId = (int) $paymentMethod->id;

        $this->customerRepository->updatePaymentMethodExcludedCustomerGroups(
            $livePaymentMethodId,
            $this->customerRepository->getExcludedCustomerGroupIds($testPaymentMethodId)
        );

        $this->countryRepository->updatePaymentMethodCountries(
            $livePaymentMethodId,
            $this->countryRepository->getMethodCountryIds($testPaymentMethodId)
        );

        $this->countryRepository->updatePaymentMethodExcludedCountries(
            $livePaymentMethodId,
            $this->countryRepository->getExcludedCountryIds($testPaymentMethodId)
        );
    }

    /**
     * Rewrites live positions densely: copied methods first, in the test order,
     * then remaining live-only methods in their previous relative order.
     *
     * @param string[] $copiedMethodIds
     */
    private function mirrorTestOrder(int $shopId, array $copiedMethodIds): void
    {
        $liveRows = $this->paymentMethodRepository->getMethodsForCheckout(Config::ENVIRONMENT_LIVE, $shopId) ?: [];

        $rowsByMethodId = [];

        foreach ($liveRows as $liveRow) {
            $rowsByMethodId[$liveRow['id_method']] = $liveRow;
        }

        $orderedRows = [];

        foreach ($copiedMethodIds as $methodId) {
            if (!isset($rowsByMethodId[$methodId])) {
                continue;
            }

            $orderedRows[] = $rowsByMethodId[$methodId];
            unset($rowsByMethodId[$methodId]);
        }

        $liveOnlyRows = array_values($rowsByMethodId);

        usort($liveOnlyRows, static function (array $a, array $b): int {
            return (int) $a['position'] <=> (int) $b['position'];
        });

        $position = 0;

        foreach (array_merge($orderedRows, $liveOnlyRows) as $row) {
            ++$position;

            Db::getInstance()->update(
                'mol_payment_method',
                ['position' => $position],
                'id_payment_method = ' . (int) $row['id_payment_method']
            );
        }
    }

    private function copyEnvironmentPairedConfigs(int $shopId): void
    {
        $environmentPairedConfigs = [Config::MOLLIE_IFRAME, Config::MOLLIE_SINGLE_CLICK_PAYMENT];

        foreach ($environmentPairedConfigs as $configPair) {
            $sandboxValue = (int) (bool) $this->configuration->get($configPair['sandbox'], $shopId);

            $this->configuration->updateValue($configPair['production'], $sandboxValue, $shopId);
        }
    }

    /**
     * Method ids activated on the live account, or null when the live key is missing or invalid.
     *
     * @return string[]|null
     */
    private function getLiveActivatedMethodIds(int $shopId): ?array
    {
        $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY, $shopId);

        if (!$liveApiKey) {
            return null;
        }

        try {
            $api = $this->apiKeyService->setApiKey($liveApiKey, $this->module->version, false, Config::ENVIRONMENT_LIVE);
        } catch (\Exception $e) {
            $api = null;
        }

        if (!$api) {
            return null;
        }

        try {
            $apiMethods = $api->methods->allAvailable(['locale' => ''])->getArrayCopy();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s - Failed to fetch live account methods', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            return null;
        }

        $methodIds = [];

        foreach ($apiMethods as $apiMethod) {
            if ($apiMethod->status !== 'activated') {
                continue;
            }

            $methodIds[] = $apiMethod->id;
        }

        return $methodIds;
    }
}
