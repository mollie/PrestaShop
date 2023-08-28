<?php

declare(strict_types=1);

namespace Mollie\ServiceProvider;

use League\Container\Container;
use Mollie;
use Mollie\Builder\ApiTestFeedbackBuilder;
use Mollie\Factory\ModuleFactory;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandler;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandlerInterface;
use Mollie\Handler\CartRule\CartRuleQuantityChangeHandler;
use Mollie\Handler\CartRule\CartRuleQuantityChangeHandlerInterface;
use Mollie\Handler\Certificate\ApplePayDirectCertificateHandler;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Handler\PaymentOption\PaymentOptionHandler;
use Mollie\Handler\PaymentOption\PaymentOptionHandlerInterface;
use Mollie\Handler\Settings\PaymentMethodPositionHandler;
use Mollie\Handler\Settings\PaymentMethodPositionHandlerInterface;
use Mollie\Handler\Shipment\ShipmentSenderHandler;
use Mollie\Handler\Shipment\ShipmentSenderHandlerInterface;
use Mollie\Install\UninstallerInterface;
use Mollie\Logger\PrestaLogger;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\CustomLogoProviderInterface;
use Mollie\Provider\EnvironmentVersionProvider;
use Mollie\Provider\EnvironmentVersionProviderInterface;
use Mollie\Provider\OrderTotal\OrderTotalProvider;
use Mollie\Provider\OrderTotal\OrderTotalProviderInterface;
use Mollie\Provider\PaymentFeeProvider;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Provider\PaymentType\PaymentTypeIdentificationProviderInterface;
use Mollie\Provider\PaymentType\RegularInterfacePaymentTypeIdentification;
use Mollie\Provider\PhoneNumberProvider;
use Mollie\Provider\PhoneNumberProviderInterface;
use Mollie\Provider\ProfileIdProvider;
use Mollie\Provider\ProfileIdProviderInterface;
use Mollie\Provider\Shipment\AutomaticShipmentSenderStatusesProvider;
use Mollie\Provider\Shipment\AutomaticShipmentSenderStatusesProviderInterface;
use Mollie\Provider\UpdateMessageProvider;
use Mollie\Provider\UpdateMessageProviderInterface;
use Mollie\Repository\AddressRepository;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CarrierRepository;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\CartRepository;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Repository\CartRuleRepository;
use Mollie\Repository\CartRuleRepositoryInterface;
use Mollie\Repository\CurrencyRepository;
use Mollie\Repository\CurrencyRepositoryInterface;
use Mollie\Repository\CustomerRepository;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Repository\GenderRepository;
use Mollie\Repository\GenderRepositoryInterface;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\MolOrderPaymentFeeRepository;
use Mollie\Repository\MolOrderPaymentFeeRepositoryInterface;
use Mollie\Repository\OrderRepository;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Repository\PendingOrderCartRuleRepository;
use Mollie\Repository\PendingOrderCartRuleRepositoryInterface;
use Mollie\Repository\TaxRepository;
use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepository;
use Mollie\Repository\TaxRuleRepositoryInterface;
use Mollie\Repository\TaxRulesGroupRepository;
use Mollie\Repository\TaxRulesGroupRepositoryInterface;
use Mollie\Service\ApiKeyService;
use Mollie\Service\Content\SmartyTemplateParser;
use Mollie\Service\Content\TemplateParserInterface;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\AmountPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\B2bPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\BasePaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\EnvironmentVersionSpecificPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\VoucherPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidationInterface;
use Mollie\Service\PaymentMethod\PaymentMethodSortProvider;
use Mollie\Service\PaymentMethod\PaymentMethodSortProviderInterface;
use Mollie\Service\Shipment\ShipmentInformationSender;
use Mollie\Service\Shipment\ShipmentInformationSenderInterface;
use Mollie\Service\ShipmentService;
use Mollie\Service\ShipmentServiceInterface;
use Mollie\Subscription\Grid\Accessibility\SubscriptionCancelAccessibility;
use Mollie\Subscription\Install\Installer;
use Mollie\Subscription\Install\InstallerInterface;
use Mollie\Subscription\Logger\Logger;
use Mollie\Subscription\Logger\LoggerInterface;
use Mollie\Subscription\Repository\RecurringOrderRepository;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Repository\RecurringOrdersProductRepository;
use Mollie\Subscription\Repository\RecurringOrdersProductRepositoryInterface;
use Mollie\Subscription\Utility\Clock;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Utility\Decoder\DecoderInterface;
use Mollie\Utility\Decoder\JsonDecoder;
use Mollie\Verification\PaymentType\CanBeRegularPaymentType;
use Mollie\Verification\PaymentType\PaymentTypeVerificationInterface;
use Mollie\Verification\Shipment\CanSendShipment;
use Mollie\Verification\Shipment\ShipmentVerificationInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;

/**
 * Load base services here which are usually required
 */
final class BaseServiceProvider
{
    private $extendedServices;

    public function __construct($extendedServices)
    {
        $this->extendedServices = $extendedServices;
    }

    public function register(Container $container)
    {
        /* Logger */
        $this->addService($container, LoggerInterface::class, $container->get(Logger::class));
        $this->addService($container, PrestaLoggerInterface::class, $container->get(PrestaLogger::class));

        /* Utility */
        $this->addService($container, ClockInterface::class, $container->get(Clock::class));

        $this->addService($container, PaymentMethodRepositoryInterface::class, $container->get(PaymentMethodRepository::class));
        $this->addService($container, GenderRepositoryInterface::class, $container->get(GenderRepository::class));
        $this->addService($container, MolCustomerRepository::class, MolCustomerRepository::class)
            ->withArgument('MolCustomer');

        $this->addService($container, UninstallerInterface::class, $container->get(Mollie\Install\DatabaseTableUninstaller::class));

        $this->addService($container, InstallerInterface::class, Installer::class)
            ->withArguments([
                $container->get(Mollie\Subscription\Install\DatabaseTableInstaller::class),
                $container->get(Mollie\Subscription\Install\AttributeInstaller::class),
                $container->get(Mollie\Subscription\Install\HookInstaller::class),
            ]);

        $this->addService($container, DecoderInterface::class, JsonDecoder::class);

        /* shipping */
        $this->addService($container, ShipmentServiceInterface::class, $container->get(ShipmentService::class));
        $this->addService($container, PaymentTypeIdentificationProviderInterface::class, $container->get(RegularInterfacePaymentTypeIdentification::class));
        $this->addService($container, AutomaticShipmentSenderStatusesProviderInterface::class, $container->get(AutomaticShipmentSenderStatusesProvider::class));
        $this->addService($container, PaymentTypeVerificationInterface::class, $container->get(CanBeRegularPaymentType::class));
        $this->addService($container, OrderEndpointPaymentTypeHandlerInterface::class, $container->get(OrderEndpointPaymentTypeHandler::class));
        $this->addService($container, ShipmentVerificationInterface::class, $container->get(CanSendShipment::class));
        $this->addService($container, ShipmentInformationSenderInterface::class, $container->get(ShipmentInformationSender::class));
        $this->addService($container, ShipmentSenderHandlerInterface::class, ShipmentSenderHandler::class)
            ->withArguments(
                [
                    $container->get(ShipmentVerificationInterface::class),
                    $container->get(ShipmentInformationSenderInterface::class),
                ]
            );

        $this->addService($container, AddressRepositoryInterface::class, $container->get(AddressRepository::class));
        $this->addService($container, TaxRulesGroupRepositoryInterface::class, $container->get(TaxRulesGroupRepository::class));
        $this->addService($container, TaxRuleRepositoryInterface::class, $container->get(TaxRuleRepository::class));
        $this->addService($container, TaxRepositoryInterface::class, $container->get(TaxRepository::class));
        $this->addService($container, CartRepositoryInterface::class, $container->get(CartRepository::class));

        $this->addService($container, OrderTotalProviderInterface::class, $container->get(OrderTotalProvider::class));
        $this->addService($container, PaymentFeeProviderInterface::class, $container->get(PaymentFeeProvider::class));

        $this->addService($container, EnvironmentVersionProviderInterface::class, $container->get(EnvironmentVersionProvider::class));

        $this->addService($container, AccessibilityCheckerInterface::class, $container->get(SubscriptionCancelAccessibility::class));

        $this->addService($container, PendingOrderCartRuleRepositoryInterface::class, $container->get(PendingOrderCartRuleRepository::class));
        $this->addService($container, CartRuleRepositoryInterface::class, $container->get(CartRuleRepository::class));
        $this->addService($container, OrderRepositoryInterface::class, $container->get(OrderRepository::class));
        $this->addService($container, CurrencyRepositoryInterface::class, $container->get(CurrencyRepository::class));
        $this->addService($container, CustomerRepositoryInterface::class, $container->get(CustomerRepository::class));
        $this->addService($container, MolOrderPaymentFeeRepositoryInterface::class, $container->get(MolOrderPaymentFeeRepository::class));
        $this->addService($container, CarrierRepositoryInterface::class, $container->get(CarrierRepository::class));
        $this->addService($container, CartRuleQuantityChangeHandlerInterface::class, $container->get(CartRuleQuantityChangeHandler::class));

        $this->addService($container, RecurringOrderRepositoryInterface::class, RecurringOrderRepository::class)
            ->withArgument('MolRecurringOrder');
        $this->addService($container, RecurringOrdersProductRepositoryInterface::class, RecurringOrdersProductRepository::class)
            ->withArgument('MolRecurringOrdersProduct');

        $this->addService($container, TemplateParserInterface::class, SmartyTemplateParser::class);

        $this->addService($container, UpdateMessageProviderInterface::class, $container->get(UpdateMessageProvider::class));

        $this->addService($container, PaymentMethodSortProviderInterface::class, PaymentMethodSortProvider::class);
        $this->addService($container, PhoneNumberProviderInterface::class, PhoneNumberProvider::class);
        $this->addService($container, PaymentMethodRestrictionValidationInterface::class, PaymentMethodRestrictionValidation::class)
            ->withArgument([
                $container->get(BasePaymentMethodRestrictionValidator::class),
                $container->get(VoucherPaymentMethodRestrictionValidator::class),
                $container->get(EnvironmentVersionSpecificPaymentMethodRestrictionValidator::class),
                $container->get(ApplePayPaymentMethodRestrictionValidator::class),
                $container->get(AmountPaymentMethodRestrictionValidator::class),
                $container->get(B2bPaymentMethodRestrictionValidator::class),
            ]);

        $this->addService($container, CustomLogoProviderInterface::class, $container->get(CreditCardLogoProvider::class));

        $this->addService($container, PaymentMethodPositionHandlerInterface::class, PaymentMethodPositionHandler::class)
            ->withArgument(PaymentMethodRepositoryInterface::class);

        $this->addService($container, CertificateHandlerInterface::class, ApplePayDirectCertificateHandler::class)
            ->withArgument(Mollie::class);

        $this->addService($container, ProfileIdProviderInterface::class, ProfileIdProvider::class);

        $this->addService($container, PaymentOptionHandlerInterface::class, $container->get(PaymentOptionHandler::class));

        $this->addService($container, ApiTestFeedbackBuilder::class, ApiTestFeedbackBuilder::class)
            ->withArgument($container->get(ModuleFactory::class)->getModuleVersion() ?? '')
            ->withArgument(ApiKeyService::class);
    }

    private function addService(Container $container, $className, $service)
    {
        return $container->add($className, $this->getService($className, $service));
    }

    //NOTE need to call this as extended services should be initialized everywhere.
    public function getService($className, $service)
    {
        if (isset($this->extendedServices[$className])) {
            return $this->extendedServices[$className];
        }

        return $service;
    }
}
