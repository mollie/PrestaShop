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

namespace Mollie\Tests\Unit\Handler\PaymentMethod;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Handler\Certificate\Exception\ApplePayDirectCertificateCreation;
use Mollie\Handler\PaymentMethod\PaymentMethodSettingsHandler;
use Mollie\Logger\LoggerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class PaymentMethodSettingsHandlerApplePayTest extends TestCase
{
    /** @var CertificateHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $certificateHandler;

    /** @var ConfigurationAdapter|\PHPUnit\Framework\MockObject\MockObject */
    private $configuration;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var array */
    private $savedConfiguration = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->certificateHandler = $this->createMock(CertificateHandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configuration = $this->createMock(ConfigurationAdapter::class);
        $this->configuration->method('updateValue')->willReturnCallback(function ($key, $value) {
            $this->savedConfiguration[$key] = $value;
        });
    }

    public function testFailedRefreshKeepsApplePayDirectEnabled(): void
    {
        $this->certificateHandler->method('refresh')->willThrowException(
            new ApplePayDirectCertificateCreation('Failed to update', ApplePayDirectCertificateCreation::FILE_COPY_EXCEPTON)
        );

        $this->logger->expects($this->once())->method('warning');
        $this->logger->expects($this->never())->method('error');

        $this->handleApplePaySettings(['directProduct' => true, 'directCart' => true]);

        $this->assertSame(1, $this->savedConfiguration[Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT]);
        $this->assertSame(1, $this->savedConfiguration[Config::MOLLIE_APPLE_PAY_DIRECT_CART]);
    }

    public function testFileConflictStillDisablesApplePayDirect(): void
    {
        $this->certificateHandler->method('handle')->willThrowException(
            new ApplePayDirectCertificateCreation('Conflict', ApplePayDirectCertificateCreation::FILE_CONFLICT_EXCEPTION)
        );
        $this->certificateHandler->expects($this->never())->method('refresh');

        $this->expectException(MollieException::class);

        try {
            $this->handleApplePaySettings(['directProduct' => true, 'directCart' => false]);
        } finally {
            $this->assertSame(0, $this->savedConfiguration[Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT]);
            $this->assertSame(0, $this->savedConfiguration[Config::MOLLIE_APPLE_PAY_DIRECT_CART]);
        }
    }

    public function testCertificateIsNotTouchedWhenApplePayDirectIsDisabled(): void
    {
        $this->certificateHandler->expects($this->never())->method('handle');
        $this->certificateHandler->expects($this->never())->method('refresh');

        $this->handleApplePaySettings(['directProduct' => false, 'directCart' => false]);
    }

    private function handleApplePaySettings(array $settings): void
    {
        $module = $this->createMock(Mollie::class);
        $module->method('l')->willReturnArgument(0);

        // CountryRepository and CustomerRepository are final, so the handler is built without its
        // constructor and only gets the collaborators handleApplePaySettings() uses.
        $handler = (new ReflectionClass(PaymentMethodSettingsHandler::class))->newInstanceWithoutConstructor();

        $dependencies = [
            'configuration' => $this->configuration,
            'logger' => $this->logger,
            'module' => $module,
            'applePayDirectCertificateHandler' => $this->certificateHandler,
        ];

        foreach ($dependencies as $name => $dependency) {
            $property = new ReflectionProperty(PaymentMethodSettingsHandler::class, $name);
            $property->setAccessible(true);
            $property->setValue($handler, $dependency);
        }

        $method = new ReflectionMethod(PaymentMethodSettingsHandler::class, 'handleApplePaySettings');
        $method->setAccessible(true);
        $method->invoke($handler, $settings);
    }
}
