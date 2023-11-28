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

namespace Mollie\Tests\Unit\Subscription\Validator;

use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Subscription\Exception\CouldNotValidateSubscriptionSettings;
use Mollie\Subscription\Exception\ExceptionCode;
use Mollie\Subscription\Validator\SubscriptionSettingsValidator;
use Mollie\Tests\Unit\BaseTestCase;

class SubscriptionSettingsValidatorTest extends BaseTestCase
{
    /** @var CarrierRepositoryInterface */
    private $carrieRepository;

    public function setUp(): void
    {
        $this->carrieRepository = $this->mock(CarrierRepositoryInterface::class);

        parent::setUp();
    }

    public function testItSuccessfullyValidatesSubscriptionSettings(): void
    {
        $this->configuration->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(true, 1);

        $carrier = $this->mock(\Carrier::class);

        $this->carrieRepository->expects($this->once())->method('findOneBy')->willReturn($carrier);

        $subscriptionSettingsValidator = new SubscriptionSettingsValidator(
            $this->configuration,
            $this->carrieRepository
        );

        $result = $subscriptionSettingsValidator->validate();

        $this->assertEquals(true, $result);
    }

    public function testItUnsuccessfullyValidatesSubscriptionSettingsSubscriptionInactive(): void
    {
        $this->configuration->expects($this->once())->method('get')->willReturn(false);

        $subscriptionSettingsValidator = new SubscriptionSettingsValidator(
            $this->configuration,
            $this->carrieRepository
        );

        $this->expectException(CouldNotValidateSubscriptionSettings::class);
        $this->expectExceptionCode(ExceptionCode::CART_SUBSCRIPTION_SERVICE_DISABLED);

        $subscriptionSettingsValidator->validate();
    }

    public function testItUnsuccessfullyValidatesSubscriptionSettingsSubscriptionCarrierInvalid(): void
    {
        $this->configuration->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(true, 1);

        $this->carrieRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $subscriptionSettingsValidator = new SubscriptionSettingsValidator(
            $this->configuration,
            $this->carrieRepository
        );

        $this->expectException(CouldNotValidateSubscriptionSettings::class);
        $this->expectExceptionCode(ExceptionCode::CART_SUBSCRIPTION_CARRIER_INVALID);

        $subscriptionSettingsValidator->validate();
    }
}
