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

use Payum\Core\GatewayFactory;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final class MollieSubscriptionGatewayFactoryTest extends TestCase
{
    private MollieSubscriptionGatewayFactory $mollieSubscriptionGatewayFactory;

    protected function setUp(): void
    {
        $this->mollieSubscriptionGatewayFactory = new MollieSubscriptionGatewayFactory();
    }

    public function testInitializable(): void
    {
        $this->assertInstanceOf(MollieSubscriptionGatewayFactory::class, $this->mollieSubscriptionGatewayFactory);
        $this->assertInstanceOf(GatewayFactory::class, $this->mollieSubscriptionGatewayFactory);
    }

    public function testPopulateConfigRun(): void
    {
        $result = $this->mollieSubscriptionGatewayFactory->createConfig([]);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('payum.factory_name', $result);
        $this->assertSame('mollie_subscription', $result['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $result);
        $this->assertSame('Mollie subscription', $result['payum.factory_title']);

        $this->assertArrayHasKey('payum.default_options', $result);
        $this->assertArrayHasKey('api_key', $result['payum.default_options']);

        $this->assertArrayHasKey('payum.required_options', $result);
        $this->assertContains('api_key_test', $result['payum.required_options']);
    }
}
