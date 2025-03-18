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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Factory;

use PHPUnit\Framework\TestCase;
use SyliusMolliePlugin\Factory\ApiCustomerFactory;
use SyliusMolliePlugin\Factory\ApiCustomerFactoryInterface;
use SyliusMolliePlugin\Request\Api\CreateCustomer;

final class ApiCustomerFactoryTest extends TestCase
{
    private ApiCustomerFactory $apiCustomerFactory;

    public function setUp(): void
    {
        $this->apiCustomerFactory = new ApiCustomerFactory();
    }

    public function testInitializable(): void
    {
        $this->assertInstanceOf(ApiCustomerFactoryInterface::class, $this->apiCustomerFactory);
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

        $this->assertInstanceOf(CreateCustomer::class, $this->apiCustomerFactory->createNew($details));
    }
}
