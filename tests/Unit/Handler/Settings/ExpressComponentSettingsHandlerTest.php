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

namespace Mollie\Tests\Unit\Handler\Settings;

use Mollie\Config\Config;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Handler\Settings\ExpressComponentSettingsHandler;
use Mollie\Logger\LoggerInterface;
use Mollie\Tests\Unit\BaseTestCase;

class ExpressComponentSettingsHandlerTest extends BaseTestCase
{
    /** @var CertificateHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $certificateHandler;
    /** @var array<string, mixed> */
    private $stored = [];
    /** @var ExpressComponentSettingsHandler */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->certificateHandler = $this->mock(CertificateHandlerInterface::class);

        $this->configuration->method('updateValue')->willReturnCallback(function ($key, $value) {
            $this->stored[$key] = $value;
        });
        $this->configuration->method('get')->willReturnCallback(function ($key) {
            return isset($this->stored[$key]) ? (string) $this->stored[$key] : null;
        });

        $module = $this->mock(\Mollie::class);
        $module->method('l')->willReturnArgument(0);

        $this->handler = new ExpressComponentSettingsHandler(
            $this->configuration,
            $this->certificateHandler,
            $this->mock(LoggerInterface::class),
            $module
        );
    }

    public function testEnablingStoresOnlyTheMasterSwitch(): void
    {
        $this->stored[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = 1;
        $this->stored[Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL] = 0;
        $this->stored[Config::MOLLIE_APPLE_PAY_DIRECT_CART] = 1;
        $this->certificateHandler->expects($this->once())->method('handle');
        $this->certificateHandler->expects($this->once())->method('refresh');

        $result = $this->handler->handle(['enabled' => true, 'paypal' => true]);

        $this->assertSame(['enabled' => true, 'applepay' => true, 'googlepay' => false, 'paypal' => false], $result['expressComponent']);
        $this->assertNull($result['warning']);
        $this->assertSame(1, $this->stored[Config::MOLLIE_APPLE_PAY_DIRECT_CART]);
        $this->assertTrue($this->handler->isEnabled());
    }

    public function testDisablingSkipsTheDomainFile(): void
    {
        $this->stored[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = 1;
        $this->certificateHandler->expects($this->never())->method('handle');

        $result = $this->handler->handle(['enabled' => false]);

        $this->assertFalse($result['expressComponent']['enabled']);
        $this->assertFalse($this->handler->isEnabled());
    }

    public function testADomainFileFailureKeepsTheComponentEnabledWithAWarning(): void
    {
        $this->stored[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = 1;
        $this->certificateHandler->method('handle')->willThrowException(new \RuntimeException('not writable'));

        $result = $this->handler->handle(['enabled' => true]);

        $this->assertTrue($result['expressComponent']['enabled']);
        $this->assertNotNull($result['warning']);
        $this->assertSame(1, $this->stored[Config::MOLLIE_EXPRESS_COMPONENT_ENABLED]);
    }

    public function testApplePayButtonOffSkipsTheDomainFile(): void
    {
        $this->stored[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = 0;
        $this->certificateHandler->expects($this->never())->method('handle');

        $result = $this->handler->handle(['enabled' => true]);

        $this->assertNull($result['warning']);
        $this->assertFalse($result['expressComponent']['applepay']);
    }
}
