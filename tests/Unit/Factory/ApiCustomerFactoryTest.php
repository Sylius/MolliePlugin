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

namespace Tests\Sylius\MolliePlugin\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactory;
use Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactoryInterface;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;

final class ApiCustomerFactoryTest extends TestCase
{
    private CreateCustomerFactory $createCustomerFactory;

    public function setUp(): void
    {
        $this->createCustomerFactory = new CreateCustomerFactory();
    }

    public function testInitializable(): void
    {
        $this->assertInstanceOf(CreateCustomerFactoryInterface::class, $this->createCustomerFactory);
    }

    public function testCreatesNewCreateCustomer(): void
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
                'customer_mollie_id' => 15,
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
        ];

        $this->assertInstanceOf(CreateCustomer::class, $this->createCustomerFactory->createNew($details));
    }
}
