<?php

use Mollie\Config\Config;
use Mollie\Provider\EnvironmentVersionProvider;
use Mollie\Repository\MethodCountryRepository;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\EnvironmentVersionSpecificPaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class VersionSpecificPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @var EnvironmentVersionProvider|PHPUnit_Framework_MockObject_MockObject
     */
    private $environmentVersionProvider;

    /**
     * @var MethodCountryRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $methodCountryRepository;

    /**
     * @var MolPaymentMethod|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethod;

    /**
     * @var \Mollie\Adapter\LegacyContext|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentMethod = $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true);

        $this->environmentVersionProvider = $this
            ->getMockBuilder(EnvironmentVersionProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->methodCountryRepository = $this
            ->getMockBuilder(MethodCountryRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->context = $this->mockContext('AT', 'EUR');
    }

    /**
     * @dataProvider getVersionSpecificPaymentRestrictionValidationDataProvider
     */
    public function testIsValid($isCountriesApplicable, $countryExcluded, $countryAvailable, $expectedResult)
    {
        $this->paymentMethod->is_countries_applicable = $isCountriesApplicable;

        $this->methodCountryRepository
            ->expects($this->any())
            ->method('checkIfMethodIsAvailableInCountry')
            ->willReturn($countryAvailable)
        ;

        $this->methodCountryRepository
            ->expects($this->any())
            ->method('checkIfCountryIsExcluded')
            ->willReturn($countryExcluded)
        ;

        $versionSpecificValidation = new EnvironmentVersionSpecificPaymentMethodRestrictionValidator(
            $this->context,
            $this->methodCountryRepository
        );

        $isValid = $versionSpecificValidation->isValid($this->paymentMethod);

        $this->assertEquals($expectedResult, $isValid);
    }

    public function getVersionSpecificPaymentRestrictionValidationDataProvider()
    {
        return [
            'All checks pass' => [
                'isCountriesApplicable' => true,
                'countryExcluded' => false,
                'countryAvailable' => true,
                'expectedResult' => true,
            ],
            'Current environment version is not applicable to validation' => [
                'isCountriesApplicable' => true,
                'countryExcluded' => false,
                'countryAvailable' => true,
                'expectedResult' => true,
            ],
            'Method country is not available by not being in available list' => [
                'isCountriesApplicable' => true,
                'countryExcluded' => false,
                'countryAvailable' => false,
                'expectedResult' => false,
            ],
            'Method country is not available by exclusion and not being applicable' => [
                'isCountriesApplicable' => false,
                'countryExcluded' => true,
                'countryAvailable' => false,
                'expectedResult' => false,
            ],
        ];
    }
}
