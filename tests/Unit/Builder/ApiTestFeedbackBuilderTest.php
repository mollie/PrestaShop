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

namespace Mollie\Tests\Unit\Builder;

use Mollie\Builder\ApiTestFeedbackBuilder;
use Mollie\Service\ApiKeyService;
use PHPUnit\Framework\TestCase;

class ApiTestFeedbackBuilderTest extends TestCase
{
    const MODULE_VERSION = '6.4.6';

    public function testItReportsAnUnusableKeyWithoutThrowing(): void
    {
        $apiKeyService = $this->createMock(ApiKeyService::class);
        $apiKeyService->method('setApiKey')->willReturn(null);

        $builder = new ApiTestFeedbackBuilder(self::MODULE_VERSION, $apiKeyService);

        $result = $builder->getApiKeyInfo('test_abcdefghijklmnopqrstuvwxyz123', true);

        self::assertFalse($result['status']);
    }

    public function testItReportsAnEmptyKeyWithoutCallingTheApi(): void
    {
        $apiKeyService = $this->createMock(ApiKeyService::class);
        $apiKeyService->expects(self::never())->method('setApiKey');

        $builder = new ApiTestFeedbackBuilder(self::MODULE_VERSION, $apiKeyService);

        $result = $builder->getApiKeyInfo('', true);

        self::assertFalse($result['status']);
    }

    /**
     * The rejected-key result carries no 'warning' key at all, so callers have to read it
     * with empty() rather than !$result['warning'].
     */
    public function testTheUnusableKeyResultCarriesNoWarningKey(): void
    {
        $apiKeyService = $this->createMock(ApiKeyService::class);
        $apiKeyService->method('setApiKey')->willReturn(null);

        $builder = new ApiTestFeedbackBuilder(self::MODULE_VERSION, $apiKeyService);

        $result = $builder->getApiKeyInfo('live_abcdefghijklmnopqrstuvwxyz123', false);

        self::assertArrayNotHasKey('warning', $result);
    }
}
