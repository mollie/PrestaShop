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

namespace Mollie\Tests\Unit\Service\ExpressComponent;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Handler\Certificate\Exception\ApplePayDirectCertificateCreation;
use Mollie\Logger\LoggerInterface;
use Mollie\Service\ExpressComponent\ExpressApplePayDomainFileService;
use PHPUnit\Framework\TestCase;

class ExpressApplePayDomainFileServiceTest extends TestCase
{
    /** @var ConfigurationAdapter|\PHPUnit\Framework\MockObject\MockObject */
    private $configuration;

    /** @var CertificateHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $certificateHandler;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->createMock(ConfigurationAdapter::class);
        $this->certificateHandler = $this->createMock(CertificateHandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testPlacesAndRefreshesTheFileWhenExpressApplePayIsOn(): void
    {
        $this->givenSettings('1', '1');

        $this->certificateHandler->expects($this->once())->method('handle');
        $this->certificateHandler->expects($this->once())->method('refresh');
        $this->logger->expects($this->never())->method('warning');

        $this->createService()->placeForExpress();
    }

    /**
     * @dataProvider provideSwitchedOffSettings
     */
    public function testLeavesTheFileAloneWhenExpressApplePayIsOff($enabled, $applePay): void
    {
        $this->givenSettings($enabled, $applePay);

        $this->certificateHandler->expects($this->never())->method('handle');
        $this->certificateHandler->expects($this->never())->method('refresh');

        $this->createService()->placeForExpress();
    }

    public function provideSwitchedOffSettings(): array
    {
        return [
            'component off' => [null, '1'],
            'component saved as 0' => ['0', '1'],
            'apple pay button off' => ['1', null],
            'both off' => [null, null],
        ];
    }

    public function testLogsAFailureInsteadOfThrowing(): void
    {
        $this->givenSettings('1', '1');

        $this->certificateHandler->method('handle')->willThrowException(
            new ApplePayDirectCertificateCreation('Conflict', ApplePayDirectCertificateCreation::FILE_CONFLICT_EXCEPTION)
        );
        $this->certificateHandler->expects($this->never())->method('refresh');
        $this->logger->expects($this->once())->method('warning');

        $this->createService()->placeForExpress();
    }

    private function givenSettings($enabled, $applePay): void
    {
        $this->configuration->method('get')->willReturnMap([
            [Config::MOLLIE_EXPRESS_COMPONENT_ENABLED, null, null, null, $enabled],
            [Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY, null, null, null, $applePay],
        ]);
    }

    private function createService(): ExpressApplePayDomainFileService
    {
        return new ExpressApplePayDomainFileService($this->configuration, $this->certificateHandler, $this->logger);
    }
}
