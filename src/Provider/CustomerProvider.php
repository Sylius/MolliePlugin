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

namespace Sylius\MolliePlugin\Provider;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class CustomerProvider implements CustomerProviderInterface
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private FactoryInterface $customerFactory,
    ) {
    }

    public function provideNewCustomer(string $email): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->createNew();

        $customer->setEmail($email);

        $this->customerRepository->add($customer);

        return $customer;
    }
}
