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

namespace Mollie\Tests\Unit\Service\PaymentMethod;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Api\MollieApiClient;
use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\CountryRepositoryInterface;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiKeyService;
use Mollie\Service\PaymentMethod\EnvironmentSettingsCopyService;
use PHPUnit\Framework\TestCase;

class EnvironmentSettingsCopyServiceTest extends TestCase
{
    const SHOP_ID = 1;

    /** @var PaymentMethodRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethodRepository;

    /** @var ApiKeyService|\PHPUnit\Framework\MockObject\MockObject */
    private $apiKeyService;

    /** @var ConfigurationAdapter|\PHPUnit\Framework\MockObject\MockObject */
    private $configuration;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->apiKeyService = $this->createMock(ApiKeyService::class);
        $this->configuration = $this->createMock(ConfigurationAdapter::class);
    }

    public function testCopyReportsLiveKeyMissingWhenNoLiveKeySaved()
    {
        $this->configuration->method('get')->with(Config::MOLLIE_API_KEY, self::SHOP_ID)->willReturn(null);
        $this->apiKeyService->expects($this->never())->method('setApiKey');
        $this->paymentMethodRepository->expects($this->never())->method('getMethodsForCheckout');
        $this->paymentMethodRepository->expects($this->never())->method('getPaymentMethodIdByMethodId');

        $result = $this->buildService()->copy(self::SHOP_ID);

        $this->assertTrue($result['liveKeyMissing']);
        $this->assertSame([], $result['copied']);
        $this->assertSame([], $result['skipped']);
    }

    public function testCopyReportsLiveKeyMissingWhenLiveKeyIsInvalid()
    {
        $this->configuration->method('get')->willReturn('live_invalidkey');
        $this->apiKeyService->method('setApiKey')->willThrowException(new \Exception('Invalid API key'));
        $this->paymentMethodRepository->expects($this->never())->method('getMethodsForCheckout');

        $result = $this->buildService()->copy(self::SHOP_ID);

        $this->assertTrue($result['liveKeyMissing']);
    }

    public function testCopyReportsLiveKeyMissingWhenLiveAccountCannotBeReached()
    {
        $methodEndpoint = $this->getMockBuilder(MethodEndpoint::class)
            ->disableOriginalConstructor()
            ->getMock();
        $methodEndpoint->method('allAvailable')->willThrowException(new \Exception('Network failure'));

        $client = $this->getMockBuilder(MollieApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->methods = $methodEndpoint;

        $this->configuration->method('get')->willReturn('live_apikey');
        $this->apiKeyService->method('setApiKey')->willReturn($client);
        $this->paymentMethodRepository->expects($this->never())->method('getMethodsForCheckout');

        $result = $this->buildService()->copy(self::SHOP_ID);

        $this->assertTrue($result['liveKeyMissing']);
    }

    private function buildService(): EnvironmentSettingsCopyService
    {
        $module = $this->createMock(Mollie::class);
        $module->version = '6.4.5';

        return new EnvironmentSettingsCopyService(
            $this->paymentMethodRepository,
            $this->createMock(CustomerRepositoryInterface::class),
            $this->createMock(CountryRepositoryInterface::class),
            $this->apiKeyService,
            $this->configuration,
            $module,
            $this->createMock(LoggerInterface::class)
        );
    }
}
