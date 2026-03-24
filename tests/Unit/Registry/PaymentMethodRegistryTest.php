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

namespace Tests\Sylius\MolliePlugin\Unit\Registry;

use Mollie\Api\Resources\Method;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Model\PaymentMethod\Swish;
use Sylius\MolliePlugin\Registry\PaymentMethodRegistry;
use Sylius\MolliePlugin\Registry\PaymentMethodRegistryInterface;

final class PaymentMethodRegistryTest extends TestCase
{
    private PaymentMethodRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new PaymentMethodRegistry();
    }

    public function testImplementsPaymentMethodRegistryInterface(): void
    {
        $this->assertInstanceOf(PaymentMethodRegistryInterface::class, $this->registry);
    }

    public function testSwishIsRegisteredInGateways(): void
    {
        $this->assertArrayHasKey(Swish::SWISH, PaymentMethodRegistryInterface::GATEWAYS);
        $this->assertSame(Swish::class, PaymentMethodRegistryInterface::GATEWAYS[Swish::SWISH]);
    }

    public function testAddsSwishMethod(): void
    {
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'swish';
        $mollieMethod->description = 'Swish';
        $mollieMethod->minimumAmount = (object) ['value' => '0.01', 'currency' => 'SEK'];
        $mollieMethod->maximumAmount = (object) ['value' => '115000.00', 'currency' => 'SEK'];
        $mollieMethod->image = (object) ['svg' => 'https://www.mollie.com/external/icons/payment-methods/swish.svg'];
        $mollieMethod->issuers = null;

        $this->registry->add($mollieMethod);

        $methods = $this->registry->getAllEnabled();

        $this->assertCount(1, $methods);
        $this->assertInstanceOf(Swish::class, $methods[0]);
        $this->assertSame('swish', $methods[0]->getMethodId());
    }

    public function testIgnoresUnknownMethod(): void
    {
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'unknown_method_that_does_not_exist';

        $this->registry->add($mollieMethod);

        $methods = $this->registry->getAllEnabled();

        $this->assertCount(0, $methods);
    }
}
