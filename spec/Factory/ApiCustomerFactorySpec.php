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

namespace spec\Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Factory\ApiCustomerFactory;
use Sylius\MolliePlugin\Factory\ApiCustomerFactoryInterface;
use Sylius\MolliePlugin\Request\Api\CreateCustomer;
use PhpSpec\ObjectBehavior;

final class ApiCustomerFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(ApiCustomerFactory::class);
        $this->shouldImplement(ApiCustomerFactoryInterface::class);
    }

    function it_creates_new_create_customer(): void
    {
        $details = [
            'amount' => [
                'value' => '445535.00',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 'ideal',
                'cartToken' => 'carttoken',
                'selected_issuer' => 'issuer',
                'methodType' => 'ORDER_API',
                'customer_mollie_id' => 15
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com'
        ];

        $this->createNew($details)->shouldHaveType(CreateCustomer::class);
    }
}
