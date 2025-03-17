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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Checker\Gateway;

use PHPUnit\Framework\TestCase;
use SyliusMolliePlugin\Checker\Gateway\MollieGatewayFactoryChecker;
use SyliusMolliePlugin\Checker\Gateway\MollieGatewayFactoryCheckerInterface;
use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Entity\GatewayConfigInterface;

final class MollieGatewayFactoryCheckerTest extends TestCase
{
    private MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker;

    protected function setUp(): void
    {
        $this->mollieGatewayFactoryChecker = new MollieGatewayFactoryChecker();
    }

    public function testReturnsTrueIfGatewayNameIsMollie(): void
    {
        $gatewayMock = $this->createMock(GatewayConfigInterface::class);
        $gatewayMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $this->assertTrue($this->mollieGatewayFactoryChecker->isMollieGateway($gatewayMock));
    }

    public function testReturnsTrueIfGatewayNameIsMollieSubscription(): void
    {
        $gatewayMock = $this->createMock(GatewayConfigInterface::class);
        $gatewayMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);

        $this->assertTrue($this->mollieGatewayFactoryChecker->isMollieGateway($gatewayMock));
    }

    public function testReturnsFalseIfGatewayNameIsNotMollie(): void
    {
        $gatewayMock = $this->createMock(GatewayConfigInterface::class);
        $gatewayMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('random_factory_name');

        $this->assertFalse($this->mollieGatewayFactoryChecker->isMollieGateway($gatewayMock));
    }
}