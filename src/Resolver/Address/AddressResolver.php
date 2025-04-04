<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SyliusMolliePlugin\Resolver\Address;

use SyliusMolliePlugin\Validator\ApplePayDirect\ApplePayAddressValidatorInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AddressResolver implements AddressResolverInterface
{
    /** @var ApplePayAddressValidatorInterface */
    private $addressValidator;

    /** @var RepositoryInterface */
    private $customerRepository;

    /** @var FactoryInterface */
    private $addressFactory;

    /** @var FactoryInterface */
    private $customerFactory;

    public function __construct(
        ApplePayAddressValidatorInterface $addressValidator,
        RepositoryInterface $customerRepository,
        FactoryInterface $addressFactory,
        FactoryInterface $customerFactory
    ) {
        $this->addressValidator = $addressValidator;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
    }

    public function resolve(array $applePayDirectAddress): AddressInterface
    {
        $this->addressValidator->validate($applePayDirectAddress);

        /** @var AddressInterface $address */
        $address = $this->addressFactory->createNew();

        $address->setCity($applePayDirectAddress['locality']);
        $address->setStreet(implode(' ', $applePayDirectAddress['addressLines']));
        $address->setPostcode($applePayDirectAddress['postalCode']);
        $address->setCountryCode($applePayDirectAddress['countryCode']);
        $address->setFirstName($applePayDirectAddress['givenName']);
        $address->setLastName($applePayDirectAddress['familyName']);

        /** @var ?CustomerInterface $customer */
        $customer = $this->customerRepository->findOneBy(['email' => $applePayDirectAddress['emailAddress']]);

        if (null === $customer) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            $customer->setEmail($applePayDirectAddress['emailAddress']);

            $this->customerRepository->add($customer);
        }

        $address->setCustomer($customer);

        return $address;
    }
}
