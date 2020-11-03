<?php

namespace Mollie\Provider;

use Address;
use Country;
use Mollie\Exception\PhoneNumberParseException;
use Mollie\Repository\ReadOnlyRepositoryInterface;
use Mollie\Utility\PhoneNumberUtility;
use MolliePrefix\Psr\Log\LoggerInterface;
use Validate;

final class PhoneNumberProvider implements PhoneNumberProviderInterface
{
    private $countryRepository;
    private $logger;

    public function __construct(
        ReadOnlyRepositoryInterface $countryRepository,
        LoggerInterface $logger
    ) {
        $this->countryRepository = $countryRepository;
        $this->logger = $logger;
    }

    public function getFromAddress(Address $address)
    {
        /** @var Country|null $country */
        $country = $this->countryRepository->findOneBy([
            'id_country' => $address->id_country
        ]);

        if (null === $country || !Validate::isLoadedObject($country)) {
            return null;
        }

        $phoneNumber = $this->getMobileOrPhone($address);

        try {
            return PhoneNumberUtility::internationalizeNumber($phoneNumber, $country->iso_code);
        } catch (PhoneNumberParseException $e) {
            $this->logger->info(
                '
                    Error occurred in mollie module when trying to send phone number to payment:
                    Seems like user entered incorrect phone number so we wont provide phone number for mollie payment
                ',
                [
                    'userEnteredPhone' => $phoneNumber,
                    'countryCode' => $country->iso_code
                ]
            );

            return null;
        }
    }

    private function getMobileOrPhone(Address $address)
    {
        return $address->phone_mobile ?: $address->phone;
    }
}
