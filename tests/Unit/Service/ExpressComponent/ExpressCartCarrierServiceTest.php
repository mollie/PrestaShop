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

use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Service\ExpressComponent\ExpressCartCarrierService;
use Mollie\Tests\Unit\BaseTestCase;

class ExpressCartCarrierServiceTest extends BaseTestCase
{
    /** @var int[] */
    private $existingAddressIds = [9];

    protected function setUp(): void
    {
        parent::setUp();

        $this->cart->id = 16;
        $this->cart->id_address_delivery = 9;
    }

    private function service(): ExpressCartCarrierService
    {
        $existingAddressIds = $this->existingAddressIds;

        return new class($this->mock(PrestaLoggerInterface::class), $existingAddressIds) extends ExpressCartCarrierService {
            /** @var int[] */
            private $existing;

            public function __construct(PrestaLoggerInterface $logger, array $existing)
            {
                parent::__construct($logger);
                $this->existing = $existing;
            }

            protected function addressExists(int $addressId): bool
            {
                return in_array($addressId, $this->existing, true);
            }
        };
    }

    public function testAChoiceUnderADeletedAddressMovesToTheDeliveryAddress(): void
    {
        $this->cart->delivery_option = json_encode([34 => '2,']);

        $this->cart->expects($this->once())->method('setDeliveryOption')->with([9 => '2,']);
        $this->cart->expects($this->once())->method('save');

        $this->assertTrue($this->service()->restoreCarrierChoice($this->cart));
    }

    /**
     * @dataProvider untouchedDeliveryOptionProvider
     */
    public function testTheCartIsLeftAloneOtherwise($deliveryOption, array $existingAddressIds): void
    {
        $this->cart->delivery_option = $deliveryOption;
        $this->existingAddressIds = $existingAddressIds;

        $this->cart->expects($this->never())->method('setDeliveryOption');
        $this->cart->expects($this->never())->method('save');

        $this->assertFalse($this->service()->restoreCarrierChoice($this->cart));
    }

    public function untouchedDeliveryOptionProvider(): array
    {
        return [
            'choice already on the delivery address' => [json_encode([9 => '2,']), [9]],
            'choice under another address that still exists' => [json_encode([12 => '2,']), [9, 12]],
            'no choice stored' => ['', [9]],
            'multi-address shipping' => [json_encode([34 => '2,', 35 => '1,']), [9]],
        ];
    }

    public function testACartWithoutADeliveryAddressIsLeftAlone(): void
    {
        $this->cart->id_address_delivery = 0;
        $this->cart->delivery_option = json_encode([34 => '2,']);

        $this->cart->expects($this->never())->method('setDeliveryOption');

        $this->assertFalse($this->service()->restoreCarrierChoice($this->cart));
    }
}
