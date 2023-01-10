<?php

declare(strict_types=1);

namespace Mollie\Subscription\ServiceProvider;

use League\Container\Container;
use Mollie;
use Mollie\Handler\Certificate\ApplePayDirectCertificateHandler;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Handler\PaymentOption\PaymentOptionHandler;
use Mollie\Handler\PaymentOption\PaymentOptionHandlerInterface;
use Mollie\Handler\Settings\PaymentMethodPositionHandler;
use Mollie\Handler\Settings\PaymentMethodPositionHandlerInterface;
use Mollie\Install\UninstallerInterface;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\CustomLogoProviderInterface;
use Mollie\Provider\EnvironmentVersionProvider;
use Mollie\Provider\EnvironmentVersionProviderInterface;
use Mollie\Provider\OrderTotalProvider;
use Mollie\Provider\OrderTotalProviderInterface;
use Mollie\Provider\PaymentFeeProvider;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Provider\PhoneNumberProvider;
use Mollie\Provider\PhoneNumberProviderInterface;
use Mollie\Provider\ProfileIdProvider;
use Mollie\Provider\ProfileIdProviderInterface;
use Mollie\Provider\UpdateMessageProvider;
use Mollie\Provider\UpdateMessageProviderInterface;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\Content\SmartyTemplateParser;
use Mollie\Service\Content\TemplateParserInterface;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\BasePaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\EnvironmentVersionSpecificPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\VoucherPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidationInterface;
use Mollie\Service\PaymentMethod\PaymentMethodSortProvider;
use Mollie\Service\PaymentMethod\PaymentMethodSortProviderInterface;
use Mollie\Subscription\Factory\CreateSubscriptionData;
use Mollie\Subscription\Logger\LoggerInterface;
use Mollie\Subscription\Logger\NullLogger;
use Mollie\Subscription\Repository\SubscriptionRepository;
use Mollie\Subscription\Repository\SubscriptionRepositoryInterface;
use Mollie\Subscription\Utility\Clock;
use Mollie\Subscription\Utility\ClockInterface;

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
        $this->addService($container, LoggerInterface::class, $container->get(NullLogger::class));
        /* Utility */
        $this->addService($container, ClockInterface::class, $container->get(Clock::class));

        $this->addService($container, PaymentMethodRepositoryInterface::class, $container->get(PaymentMethodRepository::class));
        $this->addService($container, MolCustomerRepository::class, MolCustomerRepository::class)
            ->withArgument('MolCustomer');

        $this->addService($container, UninstallerInterface::class, Mollie\Install\DatabaseTableUninstaller::class);

        $this->addService($container, CreateSubscriptionData::class, $container->get(CreateSubscriptionData::class));
        $this->addService($container, OrderTotalProviderInterface::class, $container->get(OrderTotalProvider::class));
        $this->addService($container, PaymentFeeProviderInterface::class, $container->get(PaymentFeeProvider::class));
        $this->addService($container, EnvironmentVersionProviderInterface::class, $container->get(EnvironmentVersionProvider::class));

        $this->addService($container, SubscriptionRepositoryInterface::class, SubscriptionRepository::class)
            ->withArgument('MolSubRecurringOrder');

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
            ]);

        $this->addService($container, CustomLogoProviderInterface::class, $container->get(CreditCardLogoProvider::class));

        $this->addService($container, PaymentMethodPositionHandlerInterface::class, PaymentMethodPositionHandler::class)
            ->withArgument(PaymentMethodRepositoryInterface::class);

        $this->addService($container, CertificateHandlerInterface::class, ApplePayDirectCertificateHandler::class)
            ->withArgument(Mollie::class);

        $this->addService($container, ProfileIdProviderInterface::class, ProfileIdProvider::class);

        $this->addService($container, PaymentOptionHandlerInterface::class, $container->get(PaymentOptionHandler::class));

        //todo: Try to make it work without prestashop container in services.yml. Skipping now because its taking to much time
//        $this->addService($container, HookDispatcherInterface::class, HookDispatcher::class);
//
//        $this->addService($container, SubscriptionGridQueryBuilder::class, SubscriptionGridQueryBuilder::class)
//            ->withArgument(Connection::class)
//            ->withArgument('ps_') //todo: change to adapter
//            ->withArgument('@prestashop.core.query.doctrine_search_criteria_applicator')
//        ;
//
//        $this->addService($container, SubscriptionGridDefinitionFactory::class, DoctrineGridDataFactory::class)
//            ->withArgument(SubscriptionGridQueryBuilder::class)
//            ->withArgument('@prestashop.core.hook.dispatcher')
//            ->withArgument('@prestashop.core.grid.query.doctrine_query_parser')
//            ->withArgument('invertus_mollie_subscription')
//            ->withArgument(HookDispatcher::class)
//            ->withArgument(\MollieSubscription::class)
//        ;

//        $this->addService($container, GridFactory::class, GridFactory::class)
//            ->withArgument(SubscriptionGridDefinitionFactory::class)
//            ->withArgument(SubscriptionGridQueryBuilder::class)
//            ->withArgument('@prestashop.core.grid.filter.form_factory')
//            ->withArgument('@prestashop.core.hook.dispatcher')
//        ;
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
