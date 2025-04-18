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
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;

final class MollieGatewayFactoryTest extends TestCase
{
    private MollieGatewayFactory $mollieGatewayFactory;

    protected function setUp(): void
    {
        $this->mollieGatewayFactory = new MollieGatewayFactory();
    }

    public function testInitializable(): void
    {
        $this->assertInstanceOf(MollieGatewayFactory::class, $this->mollieGatewayFactory);
        $this->assertInstanceOf(GatewayFactory::class, $this->mollieGatewayFactory);
    }

    public function testPopulateConfigRun(): void
    {
        $result = $this->mollieGatewayFactory->createConfig([]);

        $this->assertIsArray($result);

        $this->assertArrayHasKey('payum.factory_name', $result);
        $this->assertSame('mollie', $result['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $result);
        $this->assertSame('Mollie', $result['payum.factory_title']);

        $this->assertArrayHasKey('payum.default_options', $result);
        $this->assertArrayHasKey('api_key', $result['payum.default_options']);

        $this->assertArrayHasKey('payum.required_options', $result);
        $this->assertContains('api_key_test', $result['payum.required_options']);
    }
}
