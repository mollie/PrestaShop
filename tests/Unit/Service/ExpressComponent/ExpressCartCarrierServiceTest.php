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
    private $realAddressIds = [9];

    protected function setUp(): void
    {
        parent::setUp();

        $this->cart->id = 16;
        $this->cart->id_address_delivery = 9;
    }

    private function service(): ExpressCartCarrierService
    {
        return new class($this->mock(PrestaLoggerInterface::class), $this->realAddressIds) extends ExpressCartCarrierService {
            /** @var int[] */
            private $realAddressIds;

            public function __construct(PrestaLoggerInterface $logger, array $realAddressIds)
            {
                parent::__construct($logger);
                $this->realAddressIds = $realAddressIds;
            }

            protected function isLeftoverAddress(int $addressId): bool
            {
                return !in_array($addressId, $this->realAddressIds, true);
            }

            protected function isTemporaryAddress(int $addressId): bool
            {
                return !in_array($addressId, $this->realAddressIds, true);
            }
        };
    }

    public function testAChoiceUnderALeftoverAddressMovesToTheDeliveryAddress(): void
    {
        $this->cart->delivery_option = json_encode([34 => '2,']);

        $this->cart->expects($this->once())->method('setDeliveryOption')->with([9 => '2,']);
        $this->cart->expects($this->once())->method('save');

        $this->assertTrue($this->service()->restoreCarrierChoice($this->cart));
    }

    /**
     * @dataProvider untouchedDeliveryOptionProvider
     */
    public function testTheCartIsLeftAloneOtherwise($deliveryOption, array $realAddressIds): void
    {
        $this->cart->delivery_option = $deliveryOption;
        $this->realAddressIds = $realAddressIds;

        $this->cart->expects($this->never())->method('setDeliveryOption');
        $this->cart->expects($this->never())->method('save');

        $this->assertFalse($this->service()->restoreCarrierChoice($this->cart));
    }

    public function untouchedDeliveryOptionProvider(): array
    {
        return [
            'choice already on the delivery address' => [json_encode([9 => '2,']), [9]],
            'choice under another real address' => [json_encode([12 => '2,']), [9, 12]],
            'no choice stored' => ['', [9]],
            'multi-address shipping' => [json_encode([34 => '2,', 35 => '1,']), [9]],
        ];
    }

    public function testACartOnATemporaryAddressIsBeingRewritten(): void
    {
        $this->cart->id_address_delivery = 88;

        $this->assertTrue($this->service()->isCartBeingRewritten($this->cart));
    }

    public function testACartOnItsRealAddressIsNotBeingRewritten(): void
    {
        $this->assertFalse($this->service()->isCartBeingRewritten($this->cart));
    }

    public function testACartWithoutADeliveryAddressIsLeftAlone(): void
    {
        $this->cart->id_address_delivery = 0;
        $this->cart->delivery_option = json_encode([34 => '2,']);

        $this->cart->expects($this->never())->method('setDeliveryOption');

        $this->assertFalse($this->service()->restoreCarrierChoice($this->cart));
    }
}
