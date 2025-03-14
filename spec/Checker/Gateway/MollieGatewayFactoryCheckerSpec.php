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

namespace spec\Sylius\MolliePlugin\Checker\Gateway;

use Sylius\MolliePlugin\Checker\Gateway\MollieGatewayFactoryChecker;
use Sylius\MolliePlugin\Checker\Gateway\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use PhpSpec\ObjectBehavior;

final class MollieGatewayFactoryCheckerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieGatewayFactoryChecker::class);
        $this->shouldImplement(MollieGatewayFactoryCheckerInterface::class);
    }

    public function it_returns_true_if_gateway_name_is_mollie(
        GatewayConfigInterface $gateway
    ): void {
        $mollieGateways = [
            MollieGatewayFactory::FACTORY_NAME,
            MollieSubscriptionGatewayFactory::FACTORY_NAME,
        ];

        $gateway->getFactoryName()
            ->willReturn('mollie');

        $this->isMollieGateway($gateway)
            ->shouldReturn(true);
    }

    public function it_returns_true_if_gateway_name_is_mollie_subscription(
        GatewayConfigInterface $gateway
    ): void {
        $mollieGateways = [
            MollieGatewayFactory::FACTORY_NAME,
            MollieSubscriptionGatewayFactory::FACTORY_NAME,
        ];

        $gateway->getFactoryName()
            ->willReturn('mollie_subscription');

        $this->isMollieGateway($gateway)
            ->shouldReturn(true);
    }

    public function it_returns_false_if_gateway_name_is_not_mollie(
        GatewayConfigInterface $gateway
    ): void {
        $mollieGateways = [
            MollieGatewayFactory::FACTORY_NAME,
            MollieSubscriptionGatewayFactory::FACTORY_NAME,
        ];

        $gateway->getFactoryName()
            ->willReturn('random_factory_name');

        $this->isMollieGateway($gateway)
            ->shouldReturn(false);
    }
}
