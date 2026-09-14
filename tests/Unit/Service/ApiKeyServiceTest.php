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

namespace Mollie\Tests\Unit\Service;

use Mollie\Config\Config;
use Mollie\Service\ApiKeyService;
use PHPUnit\Framework\TestCase;

class ApiKeyServiceTest extends TestCase
{
    const MODULE_VERSION = '6.4.6';

    /** @var ApiKeyService */
    private $apiKeyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKeyService = new ApiKeyService();
    }

    public function testItReturnsNullWhenTheTestKeyIsTooShort(): void
    {
        // 'test_' + 29 characters: accepted by the 6.3.1 form, rejected by /^test_\w{30,}$/
        $result = $this->apiKeyService->setApiKey(
            'test_abcdefghijklmnopqrstuvwxyz123',
            self::MODULE_VERSION,
            false,
            Config::ENVIRONMENT_TEST
        );

        self::assertNull($result);
    }

    public function testItReturnsNullWhenTheLiveKeyIsTooShort(): void
    {
        $result = $this->apiKeyService->setApiKey(
            'live_abcdefghijklmnopqrstuvwxyz123',
            self::MODULE_VERSION,
            false,
            Config::ENVIRONMENT_LIVE
        );

        self::assertNull($result);
    }

    public function testItReturnsNullWhenTheKeyPrefixDoesNotMatchTheEnvironment(): void
    {
        $result = $this->apiKeyService->setApiKey(
            'test_abcdefghijklmnopqrstuvwxyz1234',
            self::MODULE_VERSION,
            false,
            Config::ENVIRONMENT_LIVE
        );

        self::assertNull($result);
    }

    public function testItReturnsAClientForAWellFormedKey(): void
    {
        $result = $this->apiKeyService->setApiKey(
            'test_abcdefghijklmnopqrstuvwxyz1234',
            self::MODULE_VERSION,
            false,
            Config::ENVIRONMENT_TEST
        );

        self::assertNotNull($result);
    }
}
